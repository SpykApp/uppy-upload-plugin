/**
 * Uppy Upload - Alpine.js component for Filament
 * Plugin: spykapps/filament-uppy-upload
 *
 * Uppy instance stored in WeakMap to avoid Alpine Proxy issues.
 * Supports multilingual UI via Uppy locale packs loaded from CDN.
 */
(function () {
    var uppyInstances = new WeakMap();
    var uppyModulePromise = null;
    var uppyLocaleCache = {};

    var UPPY_LOCALE_MAP = {
        'en': null, 'ar': 'ar_SA', 'de': 'de_DE', 'es': 'es_ES',
        'fr': 'fr_FR', 'hi': 'hi_IN', 'nl': 'nl_NL', 'pt': 'pt_BR',
        'pt_BR': 'pt_BR', 'tr': 'tr_TR', 'ur': 'ur_PK', 'zh': 'zh_CN',
        'zh_CN': 'zh_CN', 'zh_TW': 'zh_TW', 'ja': 'ja_JP', 'ko': 'ko_KR',
        'it': 'it_IT', 'pl': 'pl_PL', 'ru': 'ru_RU', 'sv': 'sv_SE',
        'da': 'da_DK', 'fi': 'fi_FI', 'nb': 'nb_NO', 'cs': 'cs_CZ',
        'el': 'el_GR', 'he': 'he_IL', 'hu': 'hu_HU', 'id': 'id_ID',
        'ro': 'ro_RO', 'sk': 'sk_SK', 'th': 'th_TH', 'uk': 'uk_UA',
        'vi': 'vi_VN',
    };

    function loadUppyModule(version) {
        if (!uppyModulePromise) {
            uppyModulePromise = import('https://releases.transloadit.com/uppy/v' + version + '/uppy.min.mjs');
        }
        return uppyModulePromise;
    }

    function loadCss(version) {
        var url = 'https://releases.transloadit.com/uppy/v' + version + '/uppy.min.css';
        if (document.querySelector('link[href="' + url + '"]')) return;
        var link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = url;
        document.head.appendChild(link);
    }

    async function loadUppyLocale(version, locale) {
        if (!locale || locale === 'en') return null;
        var uppyLocale = UPPY_LOCALE_MAP[locale] || UPPY_LOCALE_MAP[locale.split('_')[0]] || UPPY_LOCALE_MAP[locale.split('-')[0]];
        if (!uppyLocale) return null;
        if (uppyLocaleCache[uppyLocale]) return uppyLocaleCache[uppyLocale];
        try {
            var url = 'https://releases.transloadit.com/uppy/locales/v4.3.0/' + uppyLocale + '.min.js';
            var resp = await fetch(url);
            if (!resp.ok) return null;
            var text = await resp.text();
            var fn = new Function(text + '; return globalThis.Uppy && globalThis.Uppy.locales && globalThis.Uppy.locales.' + uppyLocale + ';');
            var loc = fn();
            if (loc) { uppyLocaleCache[uppyLocale] = loc; return loc; }
        } catch (e) { console.warn('[UppyUpload] Failed to load locale ' + uppyLocale + ':', e); }
        return null;
    }

    function fmtBytes(b) {
        if (!b) return '0 B';
        var k = 1024, s = ['B', 'KB', 'MB', 'GB'];
        var i = Math.floor(Math.log(b) / Math.log(k));
        return parseFloat((b / Math.pow(k, i)).toFixed(1)) + ' ' + s[i];
    }

    function guessMime(name) {
        var ext = (name.split('.').pop() || '').toLowerCase();
        return ({
            jpg: 'image/jpeg', jpeg: 'image/jpeg', png: 'image/png',
            gif: 'image/gif', webp: 'image/webp', svg: 'image/svg+xml',
            pdf: 'application/pdf', mp4: 'video/mp4', webm: 'video/webm',
            mp3: 'audio/mpeg', wav: 'audio/wav', zip: 'application/zip',
            doc: 'application/msword',
            docx: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            xls: 'application/vnd.ms-excel',
            xlsx: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        })[ext] || 'application/octet-stream';
    }

    function getCsrf() {
        var el = document.querySelector('meta[name="csrf-token"]');
        return el ? el.content : '';
    }

    async function postWithRetry(url, formData, retries) {
        retries = retries || 3;
        var csrf = getCsrf();
        var lastErr;
        for (var a = 0; a < retries; a++) {
            try {
                var r = await fetch(url, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    body: formData,
                });
                if (!r.ok) throw new Error('HTTP ' + r.status + ': ' + (await r.text()));
                return await r.json();
            } catch (e) {
                lastErr = e;
                await new Promise(function (resolve) { setTimeout(resolve, [1000, 3000, 5000][a] || 5000); });
            }
        }
        throw lastErr;
    }

    function registerComponent(Alpine) {
        if (Alpine._uppyUploadRegistered) return;
        Alpine._uppyUploadRegistered = true;

        Alpine.data('uppyUpload', function (params) {
            var config = params.config;

            return {
                state: params.state,
                isLoading: true,

                _uppy() { return uppyInstances.get(this.$refs.uppyDashboard); },

                init() {
                    var self = this;
                    this._boot().catch(function (err) { console.error('[UppyUpload] Boot failed:', err); self.isLoading = false; });
                },

                async _boot() {
                    var version = config.uppyVersion || '5.2.1';
                    loadCss(version);
                    await this.$nextTick();
                    await this._initUppy();
                },

                async _initUppy() {
                    var el = this.$refs.uppyDashboard;
                    if (!el) { this.isLoading = false; return; }

                    var old = uppyInstances.get(el);
                    if (old) { try { old.cancelAll(); old.close(); } catch (e) {} uppyInstances.delete(el); }

                    var version = config.uppyVersion || '5.2.1';
                    var mod = await loadUppyModule(version);
                    var localeObj = await loadUppyLocale(version, config.locale);

                    var restrictions = {};
                    if (config.maxFileSize > 0) restrictions.maxFileSize = config.maxFileSize;
                    if (config.minFiles > 0) restrictions.minNumberOfFiles = config.minFiles;
                    if (config.acceptedFileTypes && config.acceptedFileTypes.length > 0) restrictions.allowedFileTypes = config.acceptedFileTypes;
                    if (config.multiple === false) { restrictions.maxNumberOfFiles = 1; }
                    else if (config.maxFiles > 0) { restrictions.maxNumberOfFiles = config.maxFiles; }

                    var uppyOpts = { id: 'uppy-' + config.statePath.replace(/\./g, '-') + '-' + Date.now(), restrictions: restrictions, autoProceed: false };
                    if (localeObj) uppyOpts.locale = localeObj;

                    var uppy = new mod.Uppy(uppyOpts);
                    uppyInstances.set(el, uppy);

                    var note = config.note || '';
                    if (!note) {
                        var parts = [];
                        if (config.maxFileSize > 0) { var t = config.translations || {}; parts.push((t.max_file_size || 'Max') + ': ' + fmtBytes(config.maxFileSize)); }
                        if (config.acceptedFileTypes && config.acceptedFileTypes.length > 0) parts.push(config.acceptedFileTypes.join(', '));
                        note = parts.join(' · ');
                    }

                    var detectedTheme = config.theme || 'auto';
                    if (detectedTheme === 'auto') detectedTheme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';

                    uppy.use(mod.Dashboard, {
                        target: el, inline: config.inline !== false, width: '100%', height: config.height || 350,
                        showProgressDetails: true, showRemoveButtonAfterComplete: true, note: note || undefined,
                        proudlyDisplayPoweredByUppy: false, theme: detectedTheme,
                        singleFileFullScreen: config.multiple === false,
                        autoOpen: config.autoOpenFileEditor ? 'imageEditor' : null,
                        hideUploadButton: false, doneButtonHandler: null,
                    });

                    if (config.webcam !== false && mod.Webcam) uppy.use(mod.Webcam, { target: mod.Dashboard, showVideoSourceDropdown: true, showRecordingLength: true, mirror: true, modes: ['video-audio', 'video-only', 'audio-only', 'picture'] });
                    if (config.screenCapture !== false && mod.ScreenCapture) uppy.use(mod.ScreenCapture, { target: mod.Dashboard });
                    if (config.audio !== false && mod.Audio) uppy.use(mod.Audio, { target: mod.Dashboard, showRecordingLength: true });
                    if (config.imageEditor !== false && mod.ImageEditor) uppy.use(mod.ImageEditor, { target: mod.Dashboard, quality: 0.8 });
                    if (mod.Compressor) uppy.use(mod.Compressor, { quality: 0.8, limit: 10 });
                    if (config.dragDrop !== false && mod.DropTarget) { try { uppy.use(mod.DropTarget, { target: document.body }); } catch (e) {} }

                    if (config.companionUrl && mod.RemoteSources) {
                        try {
                            var rsOpts = { companionUrl: config.companionUrl };
                            if (config.remoteSources && config.remoteSources.length > 0) rsOpts.sources = config.remoteSources;
                            uppy.use(mod.RemoteSources, rsOpts);
                        } catch (e) { console.warn('[UppyUpload] RemoteSources failed:', e); }
                    }

                    var self = this;
                    uppy.addUploader(function (fileIDs) { return self._handleUpload(fileIDs); });
                    uppy.on('file-removed', function (file, reason) {
                        if (reason === 'removed-by-user' && file.response && file.response.body && file.response.body.path) {
                            self._rmState(file.response.body.path);
                            self._rmServer(file.response.body.path);
                        }
                    });

                    if (Array.isArray(this.state) && this.state.length > 0) this._syncExisting(uppy, this.state);
                    this.isLoading = false;
                },

                async _handleUpload(fileIDs) {
                    var uppy = this._uppy(); if (!uppy) return;
                    var filesToUpload = [];
                    for (var idx = 0; idx < fileIDs.length; idx++) {
                        var file = uppy.getFile(fileIDs[idx]);
                        if (file && !(file.progress && file.progress.uploadComplete)) filesToUpload.push(file);
                    }
                    if (filesToUpload.length === 0) return;
                    uppy.emit('upload-start', filesToUpload);
                    for (var i = 0; i < filesToUpload.length; i++) {
                        try { await this._uploadFile(uppy, filesToUpload[i], filesToUpload[i].id); }
                        catch (err) { console.error('[UppyUpload] Upload error:', err); uppy.emit('upload-error', filesToUpload[i], err); }
                    }
                },

                async _uploadFile(uppy, file, fid) {
                    var chunkSz = config.chunkSize || 5242880;
                    var blob = file.data, total = blob.size, chunks = Math.ceil(total / chunkSz);

                    if (chunks <= 1) {
                        var fd = new FormData();
                        fd.append('file', blob, file.name); fd.append('filename', file.name);
                        fd.append('disk', config.disk || 'public'); fd.append('directory', config.directory || 'uploads');
                        var ep = (config.uploadEndpoint || '/uppy/upload').replace(/\/upload$/, '/upload-single');
                        var res = await postWithRetry(ep, fd);
                        this._markComplete(uppy, file, fid, total, res); this._addState(res.path); return;
                    }

                    var uid = Date.now().toString(36) + Math.random().toString(36).substr(2, 9);
                    for (var i = 0; i < chunks; i++) {
                        var start = i * chunkSz, end = Math.min(start + chunkSz, total);
                        var fd = new FormData();
                        fd.append('file', blob.slice(start, end), file.name);
                        fd.append('chunk_index', i); fd.append('total_chunks', chunks); fd.append('upload_id', uid);
                        fd.append('filename', file.name); fd.append('disk', config.disk || 'public'); fd.append('directory', config.directory || 'uploads');
                        var res = await postWithRetry(config.uploadEndpoint || '/uppy/upload', fd);
                        uppy.emit('upload-progress', file, { uploader: this, bytesUploaded: end, bytesTotal: total });
                        if (res.completed) { this._markComplete(uppy, file, fid, total, res); this._addState(res.path); }
                    }
                },

                _markComplete(uppy, file, fid, size, res) {
                    uppy.setFileState(fid, { progress: { uploadComplete: true, uploadStarted: Date.now(), bytesUploaded: size, bytesTotal: size, percentage: 100 }, response: { status: 200, body: res } });
                    uppy.emit('upload-success', file, { status: 200, body: res });
                },

                _addState(path) {
                    if (!Array.isArray(this.state)) this.state = [];
                    if (this.state.indexOf(path) === -1) this.state = this.state.concat([path]);
                },

                _rmState(path) { if (Array.isArray(this.state)) this.state = this.state.filter(function (x) { return x !== path; }); },

                async _rmServer(path) {
                    if (!config.deleteEndpoint) return;
                    try { await fetch(config.deleteEndpoint, { method: 'POST', headers: { 'X-CSRF-TOKEN': getCsrf(), 'Accept': 'application/json', 'Content-Type': 'application/json' }, body: JSON.stringify({ path: path, disk: config.disk || 'public' }) }); } catch (e) {}
                },

                _syncExisting(uppy, paths) {
                    paths.forEach(function (p) {
                        var nm = p.split('/').pop();
                        try {
                            var id = uppy.addFile({ name: nm, type: guessMime(nm), data: new Blob(['']), source: 'existing', isRemote: false });
                            uppy.setFileState(id, { progress: { uploadComplete: true, uploadStarted: Date.now(), bytesUploaded: 1, bytesTotal: 1, percentage: 100 }, response: { status: 200, body: { path: p, filename: nm } } });
                        } catch (e) {}
                    });
                },

                destroy() {
                    var el = this.$refs.uppyDashboard;
                    if (el) { var uppy = uppyInstances.get(el); if (uppy) { try { uppy.cancelAll(); uppy.close(); } catch (e) {} uppyInstances.delete(el); } }
                },
            };
        });
    }

    if (window.Alpine) registerComponent(window.Alpine);
    document.addEventListener('alpine:init', function () { if (window.Alpine) registerComponent(window.Alpine); });
})();
