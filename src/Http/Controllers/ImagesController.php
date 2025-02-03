<?php

namespace Qubiqx\Drift\Http\Controllers;

use Intervention\Image\Encoders\AutoEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Qubiqx\Drift\DriftManager;
use Qubiqx\Drift\ManipulationsTransformer;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ImagesController
{
    private $imageManager;

    public function __construct(
        public DriftManager $driftManager,
        public ManipulationsTransformer $manipulationsTransformer,
    ) {
        $this->imageManager = new ImageManager(new Driver());
    }

    public function __invoke(
        string $configName,
        string $manipulations,
        string $path,
    ): Response {
        /** @var \Qubiqx\Drift\Config|null $config */
        $config = $this->driftManager
            ->configs()
            ->firstWhere('name', $configName);

        abort_if(
            is_null($config),
            Response::HTTP_NOT_FOUND,
            'Config not found',
        );

        /** @var \Qubiqx\Drift\Contracts\CachingStrategy $cachingStrategy */
        $cachingStrategy = new $config->cachingStrategy();

        if ($cachingStrategy->validate($path, $config)) {
            $cachedImage = $cachingStrategy->resolve($path, $config);
            if (str($path)->lower()->endsWith(['.png', '.jpg', '.jpeg', '.webp'])) {
                $image = $this->imageManager->read($cachedImage);

                $image = $image->encode(new AutoEncoder());
                $mime = $image->mediaType();
            } else {
                $image = $cachedImage;
            }

            return response((string)$image)->header('Content-Type', $mime ?? Storage::disk($config->filesystemDisk)->mimeType($path));
        }

        abort_unless(
            Storage::disk($config->filesystemDisk)->exists($path),
            Response::HTTP_NOT_FOUND,
            'Image not found',
        );

        $image = Storage::disk($config->filesystemDisk)->get($path);
        if (str($path)->lower()->endsWith(['.png', '.jpg', '.jpeg', '.webp'])) {
            $image = $this->imageManager->read($image);
            $encoded = $image->encode();
            $mime = $encoded->mediaType();

            foreach ($this->manipulationsTransformer->decode($manipulations) as $method => $arguments) {
                if ($method === 'widen') {
                    $image->scale(width: $arguments);
                } elseif ($method === 'heighten') {
                    $image->scale(height: $arguments);
                } elseif ($method === 'fit') {
                    $image->scale(width: $arguments[0], height: $arguments[1]);
                } elseif ($method === 'encode') {
                    $image->encodeByExtension($arguments);
                } else {
                    is_array($arguments)
                        ? $image->{$method}(...$arguments)
                        : $image->{$method}($arguments);
                }
            }

        }

        $cachingStrategy->cache($path, $image, $config);

        $image = $image->encode(new AutoEncoder());

        return response((string)$image)->header('Content-Type', $mime ?? Storage::disk($config->filesystemDisk)->mimeType($path));
    }
}
