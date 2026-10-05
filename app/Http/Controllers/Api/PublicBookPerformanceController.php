<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Quiz;
use Illuminate\Http\JsonResponse;

class PublicBookPerformanceController extends Controller
{
    public function show(Book $book): JsonResponse
    {
        $book = Book::query()
            ->withQuizPerformanceStats()
            ->whereKey($book->id)
            ->where('status', 'published')
            ->firstOrFail();

        $quizzes = Quiz::query()
            ->withPerformanceStats()
            ->withCount('questions')
            ->where('book_id', $book->id)
            ->where('status', 'published')
            ->orderBy('id')
            ->get()
            ->map(fn (Quiz $quiz): array => [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'questions_count' => (int) $quiz->questions_count,
                'readers_count' => (int) $quiz->readers_count,
                'attempts_count' => (int) $quiz->attempts_count,
                'pending_review_attempts_count' => (int) $quiz->pending_review_attempts_count,
                'graded_attempts_count' => (int) $quiz->graded_attempts_count,
                'passed_attempts_count' => (int) $quiz->passed_attempts_count,
                'average_score' => $quiz->average_score !== null ? round((float) $quiz->average_score, 1) : null,
                'pass_rate' => $quiz->pass_rate,
                'best_score' => $quiz->best_score !== null ? (int) $quiz->best_score : null,
            ])
            ->values();

        return response()->json([
            'data' => [
                'book' => [
                    'id' => $book->id,
                    'title' => $book->title,
                    'slug' => $book->slug,
                ],
                'performance' => [
                    'published_quizzes_count' => (int) $book->published_quizzes_count,
                    'readers_count' => (int) $book->quiz_readers_count,
                    'attempts_count' => (int) $book->quiz_attempts_count,
                    'pending_review_attempts_count' => (int) $book->quiz_pending_review_attempts_count,
                    'graded_attempts_count' => (int) $book->quiz_graded_attempts_count,
                    'passed_attempts_count' => (int) $book->quiz_passed_attempts_count,
                    'average_score' => $book->quiz_average_score !== null ? round((float) $book->quiz_average_score, 1) : null,
                    'pass_rate' => $book->quiz_pass_rate,
                    'best_score' => $book->quiz_best_score !== null ? (int) $book->quiz_best_score : null,
                ],
                'quizzes' => $quizzes,
            ],
        ]);
    }
}
