<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// SPA routes - serves the React app
Route::get('/app/{any?}', function () {
    return view('spa');
})->where('any', '.*');
