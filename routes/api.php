<?php

use App\Http\Controllers\Api\ApiDocsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MobileController;
use Illuminate\Support\Facades\Route;

Route::get('/documentation', [ApiDocsController::class, 'index']);
Route::get('/docs/swagger', [ApiDocsController::class, 'index']);

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [MobileController::class, 'profile']);
    Route::post('/profile/photo', [MobileController::class, 'uploadProfilePhoto']);
    Route::get('/books', [MobileController::class, 'books']);
    Route::get('/books/{book}', [MobileController::class, 'showBook']);
    Route::post('/books/{book}/progress', [MobileController::class, 'syncProgress']);
    Route::post('/quizzes/{quiz}/submit', [MobileController::class, 'submitQuiz']);
});
