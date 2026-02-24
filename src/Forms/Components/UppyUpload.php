<?php

namespace SpykApp\UppyUpload\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;

class UppyUpload extends Field
{
    protected string $view = 'uppy-upload::forms.components.uppy-upload';

    protected string|Closure $disk = '';
    protected string|Closure $directory = '';
    protected int|Closure $chunkSize = 0;
    protected int|Closure $maxFileSize = 0;
    protected int|Closure $maxFiles = 10;
    protected int|Closure $minFiles = 0;
    protected bool|Closure $multiple = false;
    protected array|Closure $acceptedFileTypes = [];
    protected bool|Closure $imageEditor = true;
    protected bool|Closure $webcam = true;
    protected bool|Closure $screenCapture = true;
    protected bool|Closure $audio = true;
    protected bool|Closure $dragDrop = true;
    protected string|Closure $uploadEndpoint = '';
    protected string|Closure|null $deleteEndpoint = '';
    protected bool|Closure $showProgressDetails = true;
    protected string|Closure $theme = 'auto';
    protected bool|Closure $autoOpenFileEditor = false;
    protected string|Closure|null $note = null;
    protected bool|Closure $showRemoveButtonAfterComplete = true;
    protected int|Closure $height = 350;
    protected bool|Closure $inline = true;
    protected string|Closure|null $companionUrl = null;
    protected array|Closure $remoteSources = [];
    protected string|Closure|null $locale = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->default([]);

        $this->afterStateHydrated(function (UppyUpload $component, $state) {
            if (is_string($state)) {
                $decoded = json_decode($state, true);
                $state = $decoded ?? ($state ? [$state] : []);
            }

            if (!is_array($state)) {
                $state = $state ? [$state] : [];
            }

            $component->state($state);
        });

        $this->dehydrateStateUsing(function ($state) {
            if (!is_array($state)) {
                return $state;
            }

            $state = array_values(array_filter($state));

            if (!$this->evaluate($this->multiple) && count($state) === 1) {
                return $state[0] ?? null;
            }

            return empty($state) ? null : $state;
        });
    }

    // ── Disk & Directory ──

    public function disk(string|Closure $disk): static
    {
        $this->disk = $disk;
        return $this;
    }

    public function getDisk(): string
    {
        $value = $this->evaluate($this->disk);
        return $value ?: config('uppy-upload.disk', 'public');
    }

    public function directory(string|Closure $directory): static
    {
        $this->directory = $directory;
        return $this;
    }

    public function getDirectory(): string
    {
        $value = $this->evaluate($this->directory);
        return $value ?: config('uppy-upload.directory', 'uploads');
    }

    // ── Chunk Size ──

    public function chunkSize(int|Closure $size): static
    {
        $this->chunkSize = $size;
        return $this;
    }

    public function getChunkSize(): int
    {
        $value = $this->evaluate($this->chunkSize);
        return $value ?: config('uppy-upload.chunk_size', 5 * 1024 * 1024);
    }

    // ── File Restrictions ──

    public function maxFileSize(int|Closure $size): static
    {
        $this->maxFileSize = $size;
        return $this;
    }

    public function getMaxFileSize(): int
    {
        return $this->evaluate($this->maxFileSize);
    }

    public function maxFiles(int|Closure $max): static
    {
        $this->maxFiles = $max;
        return $this;
    }

    public function getMaxFiles(): int
    {
        return $this->evaluate($this->maxFiles);
    }

    public function minFiles(int|Closure $min): static
    {
        $this->minFiles = $min;
        return $this;
    }

    public function getMinFiles(): int
    {
        return $this->evaluate($this->minFiles);
    }

    public function multiple(bool|Closure $condition = true): static
    {
        $this->multiple = $condition;
        return $this;
    }

    public function getMultiple(): bool
    {
        return $this->evaluate($this->multiple);
    }

    public function single(): static
    {
        return $this->multiple(false)->maxFiles(1);
    }

    public function acceptedFileTypes(array|Closure $types): static
    {
        $this->acceptedFileTypes = $types;
        return $this;
    }

    public function getAcceptedFileTypes(): array
    {
        return $this->evaluate($this->acceptedFileTypes);
    }

    public function image(): static
    {
        return $this->acceptedFileTypes(['image/*']);
    }

    public function video(): static
    {
        return $this->acceptedFileTypes(['video/*']);
    }

    public function pdf(): static
    {
        return $this->acceptedFileTypes(['application/pdf']);
    }

    // ── Plugins ──

    public function imageEditor(bool|Closure $condition = true): static
    {
        $this->imageEditor = $condition;
        return $this;
    }

    public function getImageEditor(): bool
    {
        return $this->evaluate($this->imageEditor);
    }

    public function webcam(bool|Closure $condition = true): static
    {
        $this->webcam = $condition;
        return $this;
    }

    public function getWebcam(): bool
    {
        return $this->evaluate($this->webcam);
    }

    public function screenCapture(bool|Closure $condition = true): static
    {
        $this->screenCapture = $condition;
        return $this;
    }

    public function getScreenCapture(): bool
    {
        return $this->evaluate($this->screenCapture);
    }

    public function audio(bool|Closure $condition = true): static
    {
        $this->audio = $condition;
        return $this;
    }

    public function getAudio(): bool
    {
        return $this->evaluate($this->audio);
    }

    public function dragDrop(bool|Closure $condition = true): static
    {
        $this->dragDrop = $condition;
        return $this;
    }

    public function getDragDrop(): bool
    {
        return $this->evaluate($this->dragDrop);
    }

    // ── Endpoints ──

    public function uploadEndpoint(string|Closure $endpoint): static
    {
        $this->uploadEndpoint = $endpoint;
        return $this;
    }

    public function getUploadEndpoint(): string
    {
        $value = $this->evaluate($this->uploadEndpoint);
        $prefix = config('uppy-upload.route_prefix', 'uppy');
        return $value ?: "/{$prefix}/upload";
    }

    public function deleteEndpoint(string|Closure|null $endpoint): static
    {
        $this->deleteEndpoint = $endpoint;
        return $this;
    }

    public function getDeleteEndpoint(): ?string
    {
        $value = $this->evaluate($this->deleteEndpoint);
        if ($value === null) {
            return null;
        }
        $prefix = config('uppy-upload.route_prefix', 'uppy');
        return $value ?: "/{$prefix}/delete";
    }

    // ── UI Options ──

    public function showProgressDetails(bool|Closure $condition = true): static
    {
        $this->showProgressDetails = $condition;
        return $this;
    }

    public function getShowProgressDetails(): bool
    {
        return $this->evaluate($this->showProgressDetails);
    }

    public function theme(string|Closure $theme): static
    {
        $this->theme = $theme;
        return $this;
    }

    public function getTheme(): string
    {
        return $this->evaluate($this->theme);
    }

    public function darkTheme(): static
    {
        return $this->theme('dark');
    }

    public function autoOpenFileEditor(bool|Closure $condition = true): static
    {
        $this->autoOpenFileEditor = $condition;
        return $this;
    }

    public function getAutoOpenFileEditor(): bool
    {
        return $this->evaluate($this->autoOpenFileEditor);
    }

    public function note(string|Closure|null $note): static
    {
        $this->note = $note;
        return $this;
    }

    public function getNote(): ?string
    {
        return $this->evaluate($this->note);
    }

    public function showRemoveButtonAfterComplete(bool|Closure $condition = true): static
    {
        $this->showRemoveButtonAfterComplete = $condition;
        return $this;
    }

    public function getShowRemoveButtonAfterComplete(): bool
    {
        return $this->evaluate($this->showRemoveButtonAfterComplete);
    }

    public function height(int|Closure $height): static
    {
        $this->height = $height;
        return $this;
    }

    public function getHeight(): int
    {
        return $this->evaluate($this->height);
    }

    public function inline(bool|Closure $condition = true): static
    {
        $this->inline = $condition;
        return $this;
    }

    public function getInline(): bool
    {
        return $this->evaluate($this->inline);
    }

    // ── Remote Sources ──

    public function companionUrl(string|Closure|null $url): static
    {
        $this->companionUrl = $url;
        return $this;
    }

    public function getCompanionUrl(): ?string
    {
        $value = $this->evaluate($this->companionUrl);
        return $value ?: config('uppy-upload.companion_url');
    }

    public function remoteSources(array|Closure $sources): static
    {
        $this->remoteSources = $sources;
        return $this;
    }

    public function getRemoteSources(): array
    {
        $value = $this->evaluate($this->remoteSources);
        return !empty($value) ? $value : config('uppy-upload.remote_sources', []);
    }

    // ── Locale ──

    public function locale(string|Closure|null $locale): static
    {
        $this->locale = $locale;
        return $this;
    }

    public function getLocale(): ?string
    {
        $value = $this->evaluate($this->locale);
        return $value ?: app()->getLocale();
    }

    // ── Config Builder ──

    public function getUppyConfig(): array
    {
        return [
            'statePath' => $this->getStatePath(),
            'disk' => $this->getDisk(),
            'directory' => $this->getDirectory(),
            'chunkSize' => $this->getChunkSize(),
            'maxFileSize' => $this->getMaxFileSize(),
            'maxFiles' => $this->getMaxFiles(),
            'minFiles' => $this->getMinFiles(),
            'multiple' => $this->getMultiple(),
            'acceptedFileTypes' => $this->getAcceptedFileTypes(),
            'imageEditor' => $this->getImageEditor(),
            'webcam' => $this->getWebcam(),
            'screenCapture' => $this->getScreenCapture(),
            'audio' => $this->getAudio(),
            'dragDrop' => $this->getDragDrop(),
            'uploadEndpoint' => $this->getUploadEndpoint(),
            'deleteEndpoint' => $this->getDeleteEndpoint(),
            'showProgressDetails' => $this->getShowProgressDetails(),
            'theme' => $this->getTheme(),
            'autoOpenFileEditor' => $this->getAutoOpenFileEditor(),
            'note' => $this->getNote(),
            'showRemoveButtonAfterComplete' => $this->getShowRemoveButtonAfterComplete(),
            'height' => $this->getHeight(),
            'inline' => $this->getInline(),
            'companionUrl' => $this->getCompanionUrl(),
            'remoteSources' => $this->getRemoteSources(),
            'locale' => $this->getLocale(),
            'uppyVersion' => config('uppy-upload.uppy_version', '5.2.1'),
            'translations' => $this->getTranslations(),
        ];
    }

    protected function getTranslations(): array
    {
        return [
            'loading' => __('uppy-upload::uppy.loading'),
            'drop_files' => __('uppy-upload::uppy.drop_files'),
            'browse_files' => __('uppy-upload::uppy.browse_files'),
            'or_import_from' => __('uppy-upload::uppy.or_import_from'),
            'my_device' => __('uppy-upload::uppy.my_device'),
            'camera' => __('uppy-upload::uppy.camera'),
            'screencast' => __('uppy-upload::uppy.screencast'),
            'audio' => __('uppy-upload::uppy.audio'),
            'upload' => __('uppy-upload::uppy.upload'),
            'cancel' => __('uppy-upload::uppy.cancel'),
            'complete' => __('uppy-upload::uppy.complete'),
            'upload_failed' => __('uppy-upload::uppy.upload_failed'),
            'retry' => __('uppy-upload::uppy.retry'),
            'max_file_size' => __('uppy-upload::uppy.max_file_size'),
            'remove_file' => __('uppy-upload::uppy.remove_file'),
            'uploading' => __('uppy-upload::uppy.uploading'),
            'processing' => __('uppy-upload::uppy.processing'),
        ];
    }
}
