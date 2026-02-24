![Screenshot](/art/cover-uppy.png)

<p align="center">
   <a href="https://packagist.org/packages/spykapp/filament-uppy-upload">
    <img src="https://img.shields.io/packagist/v/spykapp/filament-uppy-upload.svg?style=for-the-badge" alt="Packagist Version">
   </a>
   <a href="https://packagist.org/packages/spykapp/filament-uppy-upload">
    <img src="https://img.shields.io/packagist/dt/spykapp/filament-uppy-upload.svg?style=for-the-badge" alt="Total Downloads">
   </a>
   <a href="https://laravel.com/docs/12.x"><img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel" alt="Laravel 12"></a>
   <a href="https://php.net"><img src="https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php" alt="PHP 8.3"></a>
   <a href="https://github.com/spykapp/filament-uppy-upload/blob/main/LICENSE.md">
     <img src="https://img.shields.io/badge/License-MIT-blue.svg?style=for-the-badge" alt="License">
   </a>
</p>

# Filament Uppy Upload

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spykapps/filament-uppy-upload.svg?style=flat-square)](https://packagist.org/packages/spykapps/filament-uppy-upload)

A powerful chunked file upload field for **Filament 4 & 5** using [Uppy.js](https://uppy.io/).

## Features

- **Chunked uploads** — large files through Cloudflare/nginx (5MB chunks)
- **S3 compatible** — any Laravel disk: local, public, S3, GCS, DO Spaces
- **Remote sources** — Google Drive, OneDrive, Dropbox via Companion
- **Built-in plugins** — Webcam, Screen Capture, Audio, Image Editor, Compressor
- **Multilingual** — 11 languages included, auto-detects locale
- **Dark mode** — follows Filament theme, not OS
- **Modal support** — proper z-index in Filament modals
- **Single & multiple** — smart UI for both modes
- **Zero build step** — CDN-loaded, no npm needed

## Installation

```bash
composer require spykapps/filament-uppy-upload
```

Publish and register Filament assets:

```bash
php artisan filament:assets
```

### Publishing (optional)

**Publish config:**

```bash
php artisan vendor:publish --tag="uppy-upload-config"
```

This creates `config/uppy-upload.php` where you can set defaults for disk, directory, chunk size, companion URL, and more.

**Publish translations:**

```bash
php artisan vendor:publish --tag="uppy-upload-translations"
```

This creates `lang/vendor/uppy-upload/{locale}/uppy.php` files for all included languages. Edit them to customize any string.

**Publish views:**

```bash
php artisan vendor:publish --tag="uppy-upload-views"
```

This creates `resources/views/vendor/uppy-upload/` so you can customize the Blade template and CSS.

**Publish everything at once:**

```bash
php artisan vendor:publish --provider="SpykApp\UppyUpload\UppyUploadServiceProvider"
```

## Usage

### Basic (single file)

```php
use SpykApp\UppyUpload\Forms\Components\UppyUpload;

UppyUpload::make('document')
    ->directory('documents')
```

### Multiple files

```php
UppyUpload::make('attachments')
    ->multiple()
    ->maxFiles(10)
    ->directory('attachments')
```

### Image upload with editor

```php
UppyUpload::make('avatar')
    ->image()
    ->imageEditor()
    ->maxFileSize(5 * 1024 * 1024)
    ->directory('avatars')
```

### S3 upload

```php
UppyUpload::make('files')
    ->disk('s3')
    ->directory('uploads')
    ->multiple()
```

### With remote sources

```php
UppyUpload::make('files')
    ->companionUrl('https://companion.yoursite.com')
    ->remoteSources(['GoogleDrive', 'OneDrive', 'Url'])
    ->directory('imports')
```

### ZIP files only

```php
UppyUpload::make('archive')
    ->acceptedFileTypes(['application/zip', 'application/x-zip-compressed'])
    ->maxFileSize(500 * 1024 * 1024)
    ->directory('archives')
```

### All options

```php
UppyUpload::make('files')
    ->disk('s3')                          // Any Laravel disk
    ->directory('uploads')                 // Storage directory
    ->multiple()                           // Allow multiple files
    ->maxFiles(20)                         // Max number of files
    ->minFiles(1)                          // Min number of files
    ->maxFileSize(100 * 1024 * 1024)       // 100MB max per file
    ->chunkSize(5 * 1024 * 1024)           // 5MB chunks
    ->acceptedFileTypes(['image/*'])       // MIME types
    ->webcam(false)                        // Disable webcam
    ->screenCapture(false)                 // Disable screen capture
    ->audio(false)                         // Disable audio recording
    ->imageEditor(true)                    // Enable image editor
    ->autoOpenFileEditor()                 // Auto-open editor for images
    ->dragDrop(true)                       // Full-page drag & drop
    ->height(400)                          // Dashboard height in px
    ->theme('auto')                        // 'auto', 'light', or 'dark'
    ->locale('fr')                         // Override locale
    ->note('Upload your documents here')   // Custom note text
    ->companionUrl('https://...')          // Companion URL
    ->remoteSources(['GoogleDrive'])       // Remote sources
    ->uploadEndpoint('/custom/upload')     // Custom upload URL
    ->deleteEndpoint('/custom/delete')     // Custom delete URL
```

## Panel Plugin (optional)

Set panel-level defaults so you don't repeat config on every field:

```php
use SpykApp\UppyUpload\UppyUploadPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugin(
            UppyUploadPlugin::make()
                ->companionUrl('https://companion.yoursite.com')
                ->disk('s3')
        );
}
```

## Configuration

After publishing, edit `config/uppy-upload.php`:

```php
return [
    'disk' => env('UPPY_UPLOAD_DISK', 'public'),
    'directory' => env('UPPY_UPLOAD_DIRECTORY', 'uploads'),
    'chunk_size' => env('UPPY_UPLOAD_CHUNK_SIZE', 5 * 1024 * 1024),
    'route_prefix' => env('UPPY_UPLOAD_ROUTE_PREFIX', 'uppy'),
    'middleware' => ['web'],
    'companion_url' => env('UPPY_COMPANION_URL', null),
    'remote_sources' => ['GoogleDrive', 'OneDrive', 'Dropbox', 'Url'],
    'uppy_version' => env('UPPY_VERSION', '5.2.1'),
];
```

Or use `.env`:

```env
UPPY_UPLOAD_DISK=s3
UPPY_UPLOAD_DIRECTORY=uploads
UPPY_COMPANION_URL=https://companion.yoursite.com
```

## Companion Server (for remote sources)

Required only if you want Google Drive, OneDrive, Dropbox support.

```bash
docker run -d \
  --name companion \
  --restart unless-stopped \
  -p 127.0.0.1:3890:3890 \
  -v companion_data:/output \
  -e COMPANION_PORT=3890 \
  -e COMPANION_SECRET=your-secret-here \
  -e COMPANION_DOMAIN=companion.yoursite.com \
  -e COMPANION_PROTOCOL=https \
  -e COMPANION_DATADIR=/output \
  -e COMPANION_UPLOAD_URLS="https://yoursite.com/uppy/.*" \
  -e COMPANION_GOOGLE_KEY=your-google-client-id \
  -e COMPANION_GOOGLE_SECRET=your-google-secret \
  -e COMPANION_ONEDRIVE_KEY=your-azure-client-id \
  -e COMPANION_ONEDRIVE_SECRET=your-azure-secret \
  transloadit/companion:latest
```

**OAuth redirect URIs:**
- Google: `https://companion.yoursite.com/drive/redirect`
- OneDrive: `https://companion.yoursite.com/onedrive/redirect`

## Translations

**Included languages:** English, Arabic, French, German, Spanish, Portuguese (BR), Dutch, Turkish, Chinese (Simplified), Hindi, Urdu

**Adding a new language:**

1. Publish translations: `php artisan vendor:publish --tag="uppy-upload-translations"`
2. Copy `lang/vendor/uppy-upload/en/uppy.php` to your locale folder, e.g. `lang/vendor/uppy-upload/ja/uppy.php`
3. Translate the strings

Uppy's own UI strings (buttons, status messages) are automatically loaded from CDN based on the app locale.

## Credits

- [Sanchit Patil](https://github.com/sanchitspatil)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.