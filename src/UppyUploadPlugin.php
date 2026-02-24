<?php

namespace SpykApp\UppyUpload;

use Filament\Contracts\Plugin;
use Filament\Panel;

class UppyUploadPlugin implements Plugin
{
    protected ?string $companionUrl = null;
    protected array $remoteSources = [];
    protected ?string $disk = null;
    protected ?string $directory = null;

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    public function getId(): string
    {
        return 'uppy-upload';
    }

    public function register(Panel $panel): void
    {
        //
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public function companionUrl(?string $url): static
    {
        $this->companionUrl = $url;

        return $this;
    }

    public function getCompanionUrl(): ?string
    {
        return $this->companionUrl ?? config('uppy-upload.companion_url');
    }

    public function remoteSources(array $sources): static
    {
        $this->remoteSources = $sources;

        return $this;
    }

    public function getRemoteSources(): array
    {
        return !empty($this->remoteSources)
            ? $this->remoteSources
            : config('uppy-upload.remote_sources', []);
    }

    public function disk(?string $disk): static
    {
        $this->disk = $disk;

        return $this;
    }

    public function getDisk(): ?string
    {
        return $this->disk ?? config('uppy-upload.disk');
    }

    public function directory(?string $directory): static
    {
        $this->directory = $directory;

        return $this;
    }

    public function getDirectory(): ?string
    {
        return $this->directory ?? config('uppy-upload.directory');
    }
}
