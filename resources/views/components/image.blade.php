@props([
    'config',
    'path',
    'manipulations' => [],
])

<img
    src="{{ app(\Qubiqx\Drift\UrlBuilder::class)->url($config, $path, $manipulations) }}"
    {{ $attributes }}
>
