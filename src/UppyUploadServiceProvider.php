<?php

namespace SpykApp\UppyUpload;

use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class UppyUploadServiceProvider extends PackageServiceProvider
{
    public static string $name = 'uppy-upload';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasConfigFile()
            ->hasViews()
            ->hasTranslations()
            ->hasRoute('web');
    }

    public function packageBooted(): void
    {
        FilamentAsset::register(
            assets: [
                Js::make('uppy-upload-js', __DIR__ . '/../resources/dist/uppy-upload.js'),
            ],
            package: 'spykapps/filament-uppy-upload'
        );
    }
}
