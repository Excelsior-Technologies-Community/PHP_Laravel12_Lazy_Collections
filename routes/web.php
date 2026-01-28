<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LazyCollectionController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/lazy-users', [LazyCollectionController::class, 'index']);
