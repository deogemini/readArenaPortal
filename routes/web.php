<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\ReaderController;
use App\Http\Controllers\Api\ApiDocsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicController::class, 'index'])->name('home');
Route::get('/api/docs/swagger', [ApiDocsController::class, 'index'])->name('api.docs.swagger');
Route::get('/about', [PublicController::class, 'about'])->name('about');
Route::get('/library', [PublicController::class, 'library'])->name('library');
Route::get('/pro-arena', [PublicController::class, 'proArena'])->name('pro-arena');
Route::get('/leaderboard', [PublicController::class, 'leaderboard'])->name('leaderboard');
Route::get('/books/{slug}', [PublicController::class, 'book'])->name('books.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->isAuthor()) {
            return redirect()->route('author.dashboard');
        }

        return redirect()->route('reader.dashboard');
    })->name('dashboard');
});

Route::middleware(['auth', 'verified', 'reader'])->group(function () {
    Route::get('/reader/dashboard', [ReaderController::class, 'dashboard'])->name('reader.dashboard');
    Route::get('/reader/library', [ReaderController::class, 'library'])->name('reader.library');
    Route::get('/reader/books/{slug}/content', [ReaderController::class, 'streamBookContent'])->name('reader.books.content');
    Route::get('/reader/books/{slug}', [ReaderController::class, 'book'])->name('reader.books.show');
    Route::post('/reader/books/{slug}/shelf', [ReaderController::class, 'updateShelf'])->name('reader.books.shelf.update');
    Route::delete('/reader/books/{slug}/shelf', [ReaderController::class, 'destroyShelf'])->name('reader.books.shelf.destroy');
    Route::post('/reader/books/{slug}/bookmarks', [ReaderController::class, 'storeBookmark'])->name('reader.books.bookmarks.store');
    Route::patch('/reader/bookmarks/{bookmark}', [ReaderController::class, 'updateBookmark'])->name('reader.bookmarks.update');
    Route::delete('/reader/bookmarks/{bookmark}', [ReaderController::class, 'destroyBookmark'])->name('reader.bookmarks.destroy');
    Route::post('/reader/books/{slug}/reviews', [ReaderController::class, 'storeReview'])->name('reader.books.reviews.store');
    Route::patch('/reader/reviews/{review}', [ReaderController::class, 'updateReview'])->name('reader.reviews.update');
    Route::delete('/reader/reviews/{review}', [ReaderController::class, 'destroyReview'])->name('reader.reviews.destroy');
    Route::post('/reader/books/{slug}/pages', [ReaderController::class, 'trackPagesRead'])->name('reader.books.pages.track');
    Route::post('/reader/quizzes/{quiz}/submit', [ReaderController::class, 'submitQuiz'])->name('reader.quizzes.submit');
    Route::get('/reader/goals', [ReaderController::class, 'goals'])->name('reader.goals');
    Route::post('/reader/goals', [ReaderController::class, 'storeGoal'])->name('reader.goals.store');
    Route::patch('/reader/goals/{goal}', [ReaderController::class, 'updateGoal'])->name('reader.goals.update');
    Route::delete('/reader/goals/{goal}', [ReaderController::class, 'destroyGoal'])->name('reader.goals.destroy');
    Route::get('/reader/lessons', [ReaderController::class, 'lessons'])->name('reader.lessons');
    Route::post('/reader/lessons', [ReaderController::class, 'storeLesson'])->name('reader.lessons.store');
    Route::patch('/reader/lessons/{lesson}', [ReaderController::class, 'updateLesson'])->name('reader.lessons.update');
    Route::delete('/reader/lessons/{lesson}', [ReaderController::class, 'destroyLesson'])->name('reader.lessons.destroy');
    Route::get('/reader/recommendations', [ReaderController::class, 'recommendations'])->name('reader.recommendations');
    Route::post('/reader/recommendations', [ReaderController::class, 'storeRecommendation'])->name('reader.recommendations.store');
    Route::patch('/reader/recommendations/{recommendation}', [ReaderController::class, 'updateRecommendation'])->name('reader.recommendations.update');
    Route::delete('/reader/recommendations/{recommendation}', [ReaderController::class, 'destroyRecommendation'])->name('reader.recommendations.destroy');
    Route::get('/reader/duels', [ReaderController::class, 'duels'])->name('reader.duels');
    Route::post('/reader/duels', [ReaderController::class, 'storeDuel'])->name('reader.duels.store');
    Route::patch('/reader/duels/{duel}/respond', [ReaderController::class, 'respondToDuel'])->name('reader.duels.respond');
    Route::patch('/reader/duels/{duel}/cancel', [ReaderController::class, 'cancelDuel'])->name('reader.duels.cancel');
    Route::get('/reader/shows', [ReaderController::class, 'shows'])->name('reader.shows');
    Route::post('/reader/shows/{show}/rsvp', [ReaderController::class, 'rsvpShow'])->name('reader.shows.rsvp.store');
    Route::delete('/reader/shows/{show}/rsvp', [ReaderController::class, 'cancelShowRsvp'])->name('reader.shows.rsvp.destroy');
    Route::post('/reader/shows/{show}/applications', [ReaderController::class, 'applyToShow'])->name('reader.shows.applications.store');
    Route::delete('/reader/show-applications/{application}', [ReaderController::class, 'withdrawShowApplication'])->name('reader.show-applications.destroy');
});

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::post('/competition-videos', [AdminController::class, 'storeCompetitionVideo'])->name('admin.competition-videos.store');
    Route::patch('/competition-videos/{competitionVideo}', [AdminController::class, 'updateCompetitionVideo'])->name('admin.competition-videos.update');
    Route::delete('/competition-videos/{competitionVideo}', [AdminController::class, 'destroyCompetitionVideo'])->name('admin.competition-videos.destroy');
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
    Route::get('/books', [AdminController::class, 'books'])->name('admin.books');
    Route::post('/books', [AdminController::class, 'storeBook'])->name('admin.books.store');
    Route::patch('/books/{book}', [AdminController::class, 'updateBook'])->name('admin.books.update');
    Route::delete('/books/{book}', [AdminController::class, 'destroyBook'])->name('admin.books.destroy');
    Route::get('/quizzes', [AdminController::class, 'quizzes'])->name('admin.quizzes');
    Route::post('/quizzes', [AdminController::class, 'storeQuiz'])->name('admin.quizzes.store');
    Route::patch('/quizzes/{quiz}', [AdminController::class, 'updateQuiz'])->name('admin.quizzes.update');
    Route::post('/quizzes/{quiz}/questions', [AdminController::class, 'storeQuizQuestion'])->name('admin.quizzes.questions.store');
    Route::patch('/quiz-questions/{question}', [AdminController::class, 'updateQuizQuestion'])->name('admin.quiz-questions.update');
    Route::delete('/quiz-questions/{question}', [AdminController::class, 'destroyQuizQuestion'])->name('admin.quiz-questions.destroy');
    Route::delete('/quizzes/{quiz}', [AdminController::class, 'destroyQuiz'])->name('admin.quizzes.destroy');
    Route::get('/duels', [AdminController::class, 'duels'])->name('admin.duels');
    Route::patch('/duels/{duel}', [AdminController::class, 'updateDuelStatus'])->name('admin.duels.update');
    Route::get('/shows', [AdminController::class, 'shows'])->name('admin.shows');
    Route::post('/shows', [AdminController::class, 'storeShow'])->name('admin.shows.store');
    Route::patch('/shows/{show}', [AdminController::class, 'updateShow'])->name('admin.shows.update');
    Route::delete('/shows/{show}', [AdminController::class, 'destroyShow'])->name('admin.shows.destroy');
    Route::patch('/show-applications/{application}', [AdminController::class, 'reviewShowApplication'])->name('admin.show-applications.update');
    Route::get('/reviews', [AdminController::class, 'reviews'])->name('admin.reviews');
    Route::patch('/reviews/{review}', [AdminController::class, 'moderateReview'])->name('admin.reviews.update');
    Route::get('/packages', [AdminController::class, 'packages'])->name('admin.packages');
    Route::post('/packages', [AdminController::class, 'storePackage'])->name('admin.packages.store');
    Route::patch('/packages/{package}', [AdminController::class, 'updatePackage'])->name('admin.packages.update');
    Route::delete('/packages/{package}', [AdminController::class, 'destroyPackage'])->name('admin.packages.destroy');
    Route::get('/settings', [AdminController::class, 'settings'])->name('admin.settings');
    Route::post('/settings/sms-gateway', [AdminController::class, 'updateSmsGatewaySettings'])->name('admin.settings.sms-gateway.update');
});

Route::middleware(['auth', 'verified', 'author'])->prefix('author')->group(function () {
    Route::get('/dashboard', [AuthorController::class, 'dashboard'])->name('author.dashboard');
    Route::post('/books', [AuthorController::class, 'storeBook'])->name('author.books.store');
    Route::post('/quizzes', [AuthorController::class, 'storeQuiz'])->name('author.quizzes.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
