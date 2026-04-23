<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LazyCollectionController;

Route::get('/', function () {
    return view('welcome');
});

// ==========================
// Lazy Collection (cursor)
// ==========================
Route::get('/lazy-users', [LazyCollectionController::class, 'index']);

// ==========================
// Chunk Processing
// ==========================
Route::get('/chunk-users', [LazyCollectionController::class, 'chunkUsers']);

// ==========================
// Memory Comparison
// ==========================
Route::get('/memory-test', [LazyCollectionController::class, 'memoryComparison']);

// ==========================
// Lazy Search
// ==========================
Route::get('/search/{keyword}', [LazyCollectionController::class, 'search']);


Route::get('/stream-users', [LazyCollectionController::class, 'streamUsers']);