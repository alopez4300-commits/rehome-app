<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/app', function () {
    return '<h1>ReHome v2 SPA</h1><p>React SPA will be built here in future phases.</p><p>You successfully logged in from the admin panel!</p>';
})->middleware('auth');
