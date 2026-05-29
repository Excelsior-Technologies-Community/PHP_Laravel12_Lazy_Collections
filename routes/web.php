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

// ============ NEW FUNCTIONALITY ROUTES ============

// Batch Processing with Progress Tracking
Route::get('/batch-process', [LazyCollectionController::class, 'batchProcess']);

// CSV Export using Lazy Collection
Route::get('/export-csv', [LazyCollectionController::class, 'exportToCsv']);

// Filter with Multiple Conditions
Route::get('/advanced-filter/{keyword?}', [LazyCollectionController::class, 'advancedFilter']);

// Lazy Collection with Map & Reduce
Route::get('/aggregate-stats', [LazyCollectionController::class, 'aggregateStats']);

// Pagination with Lazy Collection
Route::get('/lazy-paginate/{page?}', [LazyCollectionController::class, 'lazyPaginate']);

// Data Transformation Pipeline
Route::get('/transform-pipeline', [LazyCollectionController::class, 'transformPipeline']);

// Email Notification Simulation
Route::get('/send-notifications', [LazyCollectionController::class, 'sendNotifications']);

// JSONL (JSON Lines) File Processing
Route::get('/process-jsonl', [LazyCollectionController::class, 'processJsonlFile']);

// Real-time Dashboard Stats
Route::get('/dashboard-stats', [LazyCollectionController::class, 'dashboardStats']);

// Batch Update with Lazy Collection
Route::get('/batch-update', [LazyCollectionController::class, 'batchUpdate']);

// Compare Performance: Lazy vs Eager
Route::get('/performance-test', [LazyCollectionController::class, 'performanceTest']);

// Add this to your routes/web.php
Route::get('/dashboard', function () {
    return view('lazy-dashboard');
});