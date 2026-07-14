<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\ReaderController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicController::class, 'index'])->name('home');
Route::get('/about', [PublicController::class, 'about'])->name('about');
Route::get('/library', [PublicController::class, 'library'])->name('library');
Route::get('/books/{slug}', [PublicController::class, 'book'])->name('books.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');
    Route::get('/reader/dashboard', [ReaderController::class, 'dashboard'])->name('reader.dashboard');
    Route::get('/reader/library', [ReaderController::class, 'library'])->name('reader.library');
    Route::get('/reader/books/{slug}', [ReaderController::class, 'book'])->name('reader.books.show');
    Route::get('/reader/goals', [ReaderController::class, 'goals'])->name('reader.goals');
    Route::get('/reader/lessons', [ReaderController::class, 'lessons'])->name('reader.lessons');
    Route::get('/reader/duels', [ReaderController::class, 'duels'])->name('reader.duels');
    Route::get('/reader/shows', [ReaderController::class, 'shows'])->name('reader.shows');
});

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/books', [AdminController::class, 'books'])->name('admin.books');
    Route::get('/quizzes', [AdminController::class, 'quizzes'])->name('admin.quizzes');
    Route::get('/duels', [AdminController::class, 'duels'])->name('admin.duels');
    Route::get('/shows', [AdminController::class, 'shows'])->name('admin.shows');
    Route::get('/settings', [AdminController::class, 'settings'])->name('admin.settings');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
