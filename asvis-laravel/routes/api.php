<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ExerciseLogController;
use App\Http\Controllers\FoodLogController;
use App\Http\Controllers\HealthScoreController;
use App\Http\Controllers\LocationController;
use Illuminate\Support\Facades\Route;

// Endpoint JSON (/api/...) untuk Postman.
// Memakai controller yang sama dengan web; controller membalas JSON
// kalau request mengirim header "Accept: application/json".

// Auth
Route::post('/register', [RegisteredUserController::class, 'store'])->name('register');
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // Lokasi: create & read all
    Route::apiResource('locations', LocationController::class)->only(['index', 'store']);

    // CRUD makanan & olahraga: index, store, show, update, destroy
    Route::apiResource('food-logs', FoodLogController::class);
    Route::apiResource('exercise-logs', ExerciseLogController::class);

    // Skor kesehatan
    Route::get('/health-scores', [HealthScoreController::class, 'index'])->name('health-scores.index');
    Route::post('/health-scores/generate', [HealthScoreController::class, 'generate'])->name('health-scores.generate');
});
