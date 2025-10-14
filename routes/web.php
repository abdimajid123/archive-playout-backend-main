<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::get('/', function () {
    return response()->json(['message' => 'API is working']);
});

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

// Simple health check endpoint for Cloud Run
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'message' => 'Hello World from GitHub Actions - Final Fixed Deployment!',
        'timestamp' => now()->toISOString(),
        'environment' => config('app.env'),
        'version' => '1.0.0'
    ]);
});

// API health check endpoint
Route::get('/api/health', function () {
    return response()->json([
        'status' => 'healthy',
        'message' => 'Hello World from GitHub Actions - Final Fixed Deployment!',
        'timestamp' => now()->toISOString(),
        'environment' => config('app.env'),
        'version' => '1.0.0'
    ]);
});

// Simple test endpoint
Route::get('/test', function () {
    return response()->json([
        'message' => 'Hello World from GitHub Actions - Final Fixed Deployment!',
        'deployment' => 'successful',
        'timestamp' => now()->toISOString()
    ]);
});
