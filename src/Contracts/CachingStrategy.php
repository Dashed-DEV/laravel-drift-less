<?php

namespace Qubiqx\Drift\Contracts;

use Qubiqx\Drift\Config;
use Intervention\Image\Image;

interface CachingStrategy
{
    public function validate(string $path, Config $config): bool;

    public function resolve(string $path, Config $config): string;

    public function cache(string $path, Image $image, Config $config): void;
}
