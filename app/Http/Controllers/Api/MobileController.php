<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use App\Models\ReadingProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use OpenApi\Annotations as OA;

class MobileController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/profile",
     *     tags={"Profile"},
     *     summary="Get the authenticated user's profile",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(response=200, description="Profile retrieved successfully")
     * )
     */
    public function profile(Request $request)
    {
        return response()->json([
            'data' => [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'role' => $request->user()->role,
                'profile_photo_url' => $request->user()->profile_photo_path
                    ? asset('storage/' . $request->user()->profile_photo_path)
                    : null,
            ],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/books",
     *     tags={"Books"},
     *     summary="List all published books",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(response=200, description="Books retrieved successfully")
     * )
     */
    public function books()
    {
        $books = Book::query()
            ->with(['authors', 'genres', 'publisher'])
            ->latest('id')
            ->get()
            ->map(function ($book) {
                return [
                    'id' => $book->id,
                    'title' => $book->title,
                    'slug' => $book->slug,
                    'description' => $book->description,
                    'cover_image' => $book->cover_image,
                    'authors' => $book->authors->pluck('name'),
                    'genres' => $book->genres->pluck('name'),
                    'publisher' => $book->publisher?->name,
                    'publication_year' => $book->publication_year,
                    'status' => $book->status,
                ];
            });

        return response()->json(['data' => $books]);
    }

    /**
     * @OA\Get(
     *     path="/api/books/{book}",
     *     tags={"Books"},
     *     summary="Get a single book with its quizzes",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="book", in="path", required=true, description="Book ID", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Book retrieved successfully")
     * )
     */
    public function showBook(Book $book)
    {
        $book->load(['authors', 'genres', 'publisher', 'quizzes' => function ($query) {
            $query->where('status', 'published')->with(['questions.answers']);
        }]);

        return response()->json([
            'data' => [
                'id' => $book->id,
                'title' => $book->title,
                'slug' => $book->slug,
                'description' => $book->description,
                'cover_image' => $book->cover_image,
                'authors' => $book->authors->pluck('name'),
                'genres' => $book->genres->pluck('name'),
                'publisher' => $book->publisher?->name,
                'publication_year' => $book->publication_year,
                'page_count' => $book->page_count,
                'language' => $book->language,
                'isbn' => $book->isbn,
                'status' => $book->status,
                'quizzes' => $book->quizzes->map(function (Quiz $quiz) {
                    return [
                        'id' => $quiz->id,
                        'title' => $quiz->title,
                        'instructions' => $quiz->instructions,
                        'pass_mark' => $quiz->pass_mark,
                        'attempt_limit' => $quiz->attempt_limit,
                        'duration_minutes' => $quiz->duration_minutes,
                        'questions' => $quiz->questions->map(function ($question) {
                            return [
                                'id' => $question->id,
                                'prompt' => $question->prompt,
                                'points' => $question->points,
                                'answers' => $question->answers->map(function ($answer) {
                                    return [
                                        'id' => $answer->id,
                                        'body' => $answer->body,
                                    ];
                                }),
                            ];
                        }),
                    ];
                }),
            ],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/books/{book}/progress",
     *     tags={"Reading"},
     *     summary="Sync reading progress for a book",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="book", in="path", required=true, description="Book ID", @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(required={"current_page"}, @OA\Property(property="current_page", type="integer", example=25))),
     *     @OA\Response(response=200, description="Progress synced successfully")
     * )
     */
    public function syncProgress(Request $request, Book $book)
    {
        $payload = $request->validate([
            'current_page' => ['required', 'integer', 'min:1'],
        ]);

        $progress = ReadingProgress::firstOrCreate(
            [
                'user_id' => $request->user()->id,
                'book_id' => $book->id,
            ],
            [
                'last_page_read' => 0,
                'pages_read_total' => 0,
            ]
        );

        $lastPage = (int) $progress->last_page_read;
        $currentPage = (int) $payload['current_page'];
        $pagesRead = max(0, $currentPage - $lastPage);

        $progress->update([
            'last_page_read' => $currentPage,
            'pages_read_total' => (int) $progress->pages_read_total + $pagesRead,
            'last_progress_at' => now(),
        ]);

        return response()->json([
            'message' => 'Progress synced',
            'data' => [
                'book_id' => $book->id,
                'current_page' => $currentPage,
                'pages_read_total' => (int) $progress->pages_read_total,
            ],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/quizzes/{quiz}/submit",
     *     tags={"Quizzes"},
     *     summary="Submit answers for a quiz",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="quiz", in="path", required=true, description="Quiz ID", @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(required={"answers"}, @OA\Property(property="answers", type="object", example={"1": 3}))),
     *     @OA\Response(response=200, description="Quiz submitted successfully")
     * )
     */
    public function submitQuiz(Request $request, Quiz $quiz)
    {
        if ($quiz->status !== 'published') {
            return response()->json(['message' => 'Quiz not found'], 404);
        }

        $request->validate([
            'answers' => ['required', 'array'],
            'answers.*' => ['required', 'integer', 'exists:quiz_answers,id'],
        ]);

        $user = $request->user();
        $attemptsCount = QuizAttempt::where('quiz_id', $quiz->id)->where('user_id', $user->id)->count();

        if ($attemptsCount >= $quiz->attempt_limit) {
            return response()->json(['message' => 'Attempt limit reached for this quiz.'], 422);
        }

        $quiz->load('questions.answers');
        $selectedAnswerIds = collect($request->input('answers', []))->map(fn ($value) => (int) $value);
        $answerMap = QuizAnswer::query()->whereIn('id', $selectedAnswerIds)->get()->keyBy('id');

        $score = 0;
        $totalPoints = 0;

        foreach ($quiz->questions as $question) {
            $totalPoints += (int) $question->points;
            $answerId = (int) ($request->input('answers.' . $question->id) ?? 0);
            $answer = $answerMap->get($answerId);

            if ($answer && (int) $answer->quiz_question_id === (int) $question->id && $answer->is_correct) {
                $score += (int) $question->points;
            }
        }

        $percentScore = $totalPoints > 0 ? (int) round(($score / $totalPoints) * 100) : 0;

        QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'user_id' => $user->id,
            'score' => $percentScore,
            'passed' => $percentScore >= (int) $quiz->pass_mark,
        ]);

        return response()->json([
            'message' => 'Quiz submitted',
            'data' => [
                'score' => $percentScore,
                'passed' => $percentScore >= (int) $quiz->pass_mark,
            ],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/profile/photo",
     *     tags={"Profile"},
     *     summary="Upload a profile photo",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(required=true, @OA\MediaType(mediaType="multipart/form-data", @OA\Schema(required={"profile_photo"}, @OA\Property(property="profile_photo", type="string", format="binary")))) ,
     *     @OA\Response(response=200, description="Profile photo uploaded successfully")
     * )
     */
    public function uploadProfilePhoto(Request $request)
    {
        $request->validate([
            'profile_photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user = $request->user();

        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        $path = $request->file('profile_photo')->store('profile-photos', 'public');
        $user->update(['profile_photo_path' => $path]);

        return response()->json([
            'message' => 'Profile photo uploaded',
            'data' => [
                'profile_photo_url' => asset('storage/' . $path),
            ],
        ]);
    }
}
