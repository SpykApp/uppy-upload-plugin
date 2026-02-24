<?php

use SpykApp\UppyUpload\Http\Controllers\UppyUploadController;
use Illuminate\Support\Facades\Route;

$prefix = config('uppy-upload.route_prefix', 'uppy');
$middleware = config('uppy-upload.middleware', ['web']);

Route::prefix($prefix)
    ->middleware($middleware)
    ->group(function () {
        Route::post('/upload', [UppyUploadController::class, 'upload'])->name('uppy.upload');
        Route::post('/upload-single', [UppyUploadController::class, 'uploadSingle'])->name('uppy.upload-single');
        Route::post('/delete', [UppyUploadController::class, 'delete'])->name('uppy.delete');
    });
