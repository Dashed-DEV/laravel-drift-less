<?php

use Qubiqx\Drift\Http\Controllers\ImagesController;
use Illuminate\Support\Facades\Route;

Route::get('__images/{configName}/{manipulations}/{path}', ImagesController::class)
    ->where('path', '.*')
    ->name('__images.manipulate');
