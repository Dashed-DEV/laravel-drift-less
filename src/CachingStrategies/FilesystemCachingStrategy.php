<?php

namespace Qubiqx\Drift\CachingStrategies;

use Qubiqx\Drift\Config;
use Qubiqx\Drift\Contracts\CachingStrategy;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Image;

class FilesystemCachingStrategy implements CachingStrategy
{
    public function validate(string $path, Config $config): bool
    {
        return Storage::exists("__images-cache/{$path}");
    }

    public function resolve(string $path, Config $config): string
    {
        return Storage::get("__images-cache/{$path}");
    }

    public function cache(string $path, Image $image, Config $config): void
    {
        Storage::put("__images-cache/{$path}", (string) $image);
    }
}
