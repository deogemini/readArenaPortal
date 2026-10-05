<?php

use App\Http\Controllers\Api\ApiDocsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MobileController;
use App\Http\Controllers\Api\PublicBookPerformanceController;
use Illuminate\Support\Facades\Route;

Route::get('/documentation', [ApiDocsController::class, 'index']);
Route::get('/docs/swagger', [ApiDocsController::class, 'index']);

Route::prefix('auth')->middleware('throttle:10,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/google', [AuthController::class, 'google']);
});

Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:5,1');
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:5,1');
Route::get('/public/books/{book}/quiz-performance', [PublicBookPerformanceController::class, 'show'])
    ->middleware('throttle:60,1');

Route::middleware(['auth:sanctum', 'track.user.activity'])->group(function () {
    Route::post('/auth/logout', [MobileController::class, 'logout']);
    Route::get('/dashboard', [MobileController::class, 'dashboard']);
    Route::get('/notifications', [MobileController::class, 'notifications']);
    Route::patch('/notifications/read-all', [MobileController::class, 'markAllNotificationsRead']);
    Route::patch('/notifications/{notification}/read', [MobileController::class, 'markNotificationRead']);
    Route::delete('/notifications/{notification}', [MobileController::class, 'deleteNotification']);
    Route::get('/notification-preferences', [MobileController::class, 'notificationPreferences']);
    Route::patch('/notification-preferences', [MobileController::class, 'updateNotificationPreferences']);

    Route::get('/profile', [MobileController::class, 'profile']);
    Route::patch('/profile', [MobileController::class, 'updateProfile']);
    Route::post('/profile/photo', [MobileController::class, 'uploadProfilePhoto']);

    Route::get('/books', [MobileController::class, 'books']);
    Route::get('/books/{book}', [MobileController::class, 'showBook']);
    Route::get('/books/{book}/content', [MobileController::class, 'bookContent'])->name('api.books.content');
    Route::post('/books/{book}/progress', [MobileController::class, 'syncProgress']);
    Route::get('/reading/progress', [MobileController::class, 'readingProgress']);
    Route::get('/shelf', [MobileController::class, 'shelf']);
    Route::put('/books/{book}/shelf', [MobileController::class, 'updateShelf']);
    Route::delete('/books/{book}/shelf', [MobileController::class, 'deleteShelf']);
    Route::get('/books/{book}/reviews', [MobileController::class, 'bookReviews']);
    Route::get('/reviews', [MobileController::class, 'reviews']);
    Route::post('/reviews', [MobileController::class, 'storeReview']);
    Route::patch('/reviews/{review}', [MobileController::class, 'updateReview']);
    Route::delete('/reviews/{review}', [MobileController::class, 'deleteReview']);
    Route::get('/bookmarks', [MobileController::class, 'bookmarks']);
    Route::post('/bookmarks', [MobileController::class, 'storeBookmark']);
    Route::patch('/bookmarks/{bookmark}', [MobileController::class, 'updateBookmark']);
    Route::delete('/bookmarks/{bookmark}', [MobileController::class, 'deleteBookmark']);

    Route::get('/goals', [MobileController::class, 'goals']);
    Route::post('/goals', [MobileController::class, 'storeGoal']);
    Route::patch('/goals/{goal}', [MobileController::class, 'updateGoal']);
    Route::delete('/goals/{goal}', [MobileController::class, 'deleteGoal']);

    Route::get('/lessons', [MobileController::class, 'lessons']);
    Route::post('/lessons', [MobileController::class, 'storeLesson']);
    Route::patch('/lessons/{lesson}', [MobileController::class, 'updateLesson']);
    Route::delete('/lessons/{lesson}', [MobileController::class, 'deleteLesson']);
    Route::get('/recommendations', [MobileController::class, 'recommendations']);
    Route::post('/recommendations', [MobileController::class, 'storeRecommendation']);
    Route::patch('/recommendations/{recommendation}', [MobileController::class, 'updateRecommendation']);
    Route::delete('/recommendations/{recommendation}', [MobileController::class, 'deleteRecommendation']);
    Route::get('/shows', [MobileController::class, 'shows']);
    Route::post('/shows/{show}/rsvp', [MobileController::class, 'showRsvp']);
    Route::delete('/shows/{show}/rsvp', [MobileController::class, 'cancelShowRsvp']);
    Route::get('/show-applications', [MobileController::class, 'showApplications']);
    Route::post('/shows/{show}/applications', [MobileController::class, 'applyToShow']);
    Route::delete('/show-applications/{application}', [MobileController::class, 'withdrawShowApplication']);
    Route::get('/leaderboard', [MobileController::class, 'leaderboard']);
    Route::get('/duels', [MobileController::class, 'duels']);
    Route::post('/duels', [MobileController::class, 'storeDuel']);
    Route::patch('/duels/{duel}/respond', [MobileController::class, 'respondToDuel']);
    Route::patch('/duels/{duel}/cancel', [MobileController::class, 'cancelDuel']);

    Route::get('/quizzes/{quiz}', [MobileController::class, 'showQuiz']);
    Route::post('/quizzes/{quiz}/submit', [MobileController::class, 'submitQuiz']);
});
