<?php

namespace Qubiqx\Drift\Contracts;

use Qubiqx\Drift\Config;
use Intervention\Image\Image;

interface CachingStrategy
{
    public function validate(string $path, string $signature, Config $config): bool;

    public function resolve(string $path, string $signature, Config $config): string;

    public function cache(string $path, string $signature, Image $image, Config $config): void;
}
