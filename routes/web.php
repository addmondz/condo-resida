<?php

use Illuminate\Support\Facades\Route;

Route::get('/favicon.ico', fn () => response()->noContent());

Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
