<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Only needed if you have web-based forms outside Filament
// Filament handles its own authentication at /admin/login

// SPA routes - serves the React app
Route::get('/app/{any?}', function () {
    return view('spa');
})->where('any', '.*');
