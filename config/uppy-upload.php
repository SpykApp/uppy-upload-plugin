<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Disk
    |--------------------------------------------------------------------------
    |
    | The default filesystem disk to store uploaded files.
    | Supports any Laravel filesystem disk: 'local', 'public', 's3', etc.
    |
    */
    'disk' => env('UPPY_UPLOAD_DISK', 'public'),

    /*
    |--------------------------------------------------------------------------
    | Default Upload Directory
    |--------------------------------------------------------------------------
    */
    'directory' => env('UPPY_UPLOAD_DIRECTORY', 'uploads'),

    /*
    |--------------------------------------------------------------------------
    | Chunk Size (bytes)
    |--------------------------------------------------------------------------
    |
    | Default chunk size for chunked uploads. 5MB works well with Cloudflare.
    |
    */
    'chunk_size' => env('UPPY_UPLOAD_CHUNK_SIZE', 5 * 1024 * 1024),

    /*
    |--------------------------------------------------------------------------
    | Route Prefix
    |--------------------------------------------------------------------------
    */
    'route_prefix' => env('UPPY_UPLOAD_ROUTE_PREFIX', 'uppy'),

    /*
    |--------------------------------------------------------------------------
    | Route Middleware
    |--------------------------------------------------------------------------
    */
    'middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Companion Server URL
    |--------------------------------------------------------------------------
    |
    | URL of the Uppy Companion server for remote sources.
    | Leave null to disable remote sources.
    |
    */
    'companion_url' => env('UPPY_COMPANION_URL', null),

    /*
    |--------------------------------------------------------------------------
    | Default Remote Sources
    |--------------------------------------------------------------------------
    |
    | Options: 'GoogleDrive', 'OneDrive', 'Dropbox', 'Box',
    |          'Instagram', 'Facebook', 'Url'
    |
    */
    'remote_sources' => ['GoogleDrive', 'OneDrive', 'Dropbox', 'Url'],

    /*
    |--------------------------------------------------------------------------
    | Uppy CDN Version
    |--------------------------------------------------------------------------
    */
    'uppy_version' => env('UPPY_VERSION', '5.2.1'),
];
