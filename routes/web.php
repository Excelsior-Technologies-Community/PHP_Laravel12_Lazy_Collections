<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LazyCollectionController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/lazy-users', [LazyCollectionController::class, 'index']);

Route::get('/chunk-users', [LazyCollectionController::class, 'chunkUsers']);

Route::get('/memory-test', [LazyCollectionController::class, 'memoryComparison']);

Route::get('/search/{keyword}', [LazyCollectionController::class, 'search']);

Route::get('/stream-users', [LazyCollectionController::class, 'streamUsers']);

Route::get('/read-file', [LazyCollectionController::class, 'readLargeFile']);

Route::get('/tap-progress', [LazyCollectionController::class, 'tapProgress']);

Route::get('/combined-sources', [LazyCollectionController::class, 'combinedSources']);