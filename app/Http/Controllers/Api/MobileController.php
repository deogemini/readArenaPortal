<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Bookmark;
use App\Models\BookReview;
use App\Models\Duel;
use App\Models\DuelAttempt;
use App\Models\Lesson;
use App\Models\LiveShow;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\ReadingGoal;
use App\Models\ReadingProgress;
use App\Models\ReaderShelf;
use App\Models\Recommendation;
use App\Models\ReaderNotification;
use App\Models\ReaderNotificationPreference;
use App\Models\ReaderLikeRequest;
use App\Models\PushDeviceToken;
use App\Models\ShowApplication;
use App\Models\ShowRsvp;
use App\Models\User;
use App\Models\UserActivityEvent;
use App\Services\ReaderContentService;
use App\Services\DuelService;
use App\Services\LeaderboardService;
use App\Services\ShowParticipationService;
use App\Services\BookContentService;
use App\Services\ReaderShelfService;
use App\Services\BookmarkService;
use App\Services\BookReviewService;
use App\Services\ReaderNotificationService;
use App\Services\QuizAnswerSelection;
use App\Services\UserActivityRecorder;
use App\Services\UserAccountDeletionService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MobileController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = $request->user();
        $attempts = QuizAttempt::where('user_id', $user->id);
        $completedQuizzes = (clone $attempts)
            ->where('passed', true)
            ->whereHas('quiz', fn ($quiz) => $quiz->where('status', 'published')->whereHas('book', fn ($book) => $book->where('status', 'published')))
            ->distinct()
            ->count('quiz_id');
        $verifiedBooksCount = app(DuelService::class)->verifiedBooks($user)->count();

        return response()->json([
            'data' => [
                'user' => $this->userData($user),
                'stats' => [
                    'total_quiz_score' => app(LeaderboardService::class)->pointsForUser($user->id),
                    'quiz_attempts_count' => (int) (clone $attempts)->count(),
                    'completed_quizzes_count' => $completedQuizzes,
                    'verified_books_count' => $verifiedBooksCount,
                    'duels_unlocked' => $verifiedBooksCount > 0,
                ],
                'continue_reading' => ReadingProgress::query()
                    ->where('user_id', $user->id)
                    ->whereHas('book', fn ($query) => $query->where('status', 'published'))
                    ->with(['book' => fn ($book) => $book->withQuizPerformanceStats()->with(['authors', 'genres', 'publisher'])])
                    ->orderByDesc('last_opened_at')
                    ->limit(5)
                    ->get()
                    ->map(fn (ReadingProgress $progress) => $this->progressData($progress)),
                'active_goals' => ReadingGoal::query()
                    ->where('user_id', $user->id)
                    ->where('status', 'active')
                    ->with('book')
                    ->latest()
                    ->limit(3)
                    ->get()
                    ->map(fn (ReadingGoal $goal) => $this->goalData($goal)),
                'upcoming_shows' => LiveShow::query()
                    ->where('status', 'scheduled')
                    ->where('start_at', '>=', now())
                    ->with('book')
                    ->orderBy('start_at')
                    ->limit(3)
                    ->get()
                    ->map(fn (LiveShow $show) => $this->showData($show)),
                'lessons' => Lesson::query()
                    ->where('user_id', $user->id)
                    ->latest()
                    ->limit(3)
                    ->get()
                    ->map(fn (Lesson $lesson) => $this->lessonData($lesson)),
                'recommendations' => Recommendation::query()
                    ->where('user_id', $user->id)
                    ->with('book')
                    ->latest()
                    ->limit(3)
                    ->get()
                    ->map(fn (Recommendation $recommendation) => $this->recommendationData($recommendation)),
                'featured_books' => Book::query()
                    ->withQuizPerformanceStats()
                    ->where('status', 'published')
                    ->where('featured', true)
                    ->with(['authors', 'genres', 'publisher'])
                    ->latest('id')
                    ->limit(6)
                    ->get()
                    ->map(fn (Book $book) => $this->bookSummary($book)),
            ],
        ]);
    }

    public function profile(Request $request)
    {
        return response()->json(['data' => $this->userData($request->user())]);
    }

    public function destroyAccount(Request $request, UserAccountDeletionService $accounts)
    {
        $payload = $request->validate(['password' => ['required', 'string']]);
        $user = $request->user();
        if (! Hash::check($payload['password'], $user->password)) {
            throw ValidationException::withMessages(['password' => 'The password is incorrect.']);
        }

        $accounts->delete($user);

        return response()->json(['message' => 'Account deleted successfully.']);
    }

    public function activity(Request $request)
    {
        $filters = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);
        $events = UserActivityEvent::query()
            ->where('user_id', $request->user()->id)
            ->latest('id')
            ->paginate($filters['per_page'] ?? 20);

        return response()->json([
            'data' => $events->getCollection()->map(fn (UserActivityEvent $event) => [
                'id' => $event->id,
                'activity_key' => $event->activity_key,
                'activity' => $event->activity_label,
                'platform' => $event->platform,
                'created_at' => $event->created_at?->toIso8601String(),
            ])->values(),
            'meta' => [
                'current_page' => $events->currentPage(),
                'last_page' => $events->lastPage(),
                'per_page' => $events->perPage(),
                'total' => $events->total(),
            ],
        ]);
    }

    public function rewardHistory(Request $request, LeaderboardService $leaderboard)
    {
        $filters = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);
        $attempts = QuizAttempt::query()
            ->with('quiz:id,title,book_id')
            ->where('user_id', $request->user()->id)
            ->where('review_status', 'graded')
            ->where('passed', true)
            ->whereHas('quiz', fn ($quizzes) => $quizzes->where('status', 'published')->whereHas('book', fn ($books) => $books->where('status', 'published')))
            ->orderBy('id')
            ->get();

        $bestScores = [];
        $balance = 0;
        $history = [];
        foreach ($attempts as $attempt) {
            $quizId = (int) $attempt->quiz_id;
            $oldBest = $bestScores[$quizId] ?? 0;
            $pointsEarned = max(0, (int) $attempt->score - $oldBest);
            if ($pointsEarned === 0) {
                continue;
            }
            $bestScores[$quizId] = max($oldBest, (int) $attempt->score);
            $balance += $pointsEarned;
            $history[] = [
                'attempt_id' => $attempt->id,
                'quiz_id' => $quizId,
                'quiz_title' => $attempt->quiz?->title,
                'score' => (int) $attempt->score,
                'points_earned' => $pointsEarned,
                'total_points' => $balance,
                'created_at' => $attempt->created_at?->toIso8601String(),
            ];
        }
        $total = count($history);
        $perPage = (int) ($filters['per_page'] ?? 20);
        $page = (int) ($filters['page'] ?? 1);
        $items = collect($history)->reverse()->slice(($page - 1) * $perPage, $perPage)->values();

        return response()->json([
            'data' => $items,
            'meta' => [
                'current_page' => $page,
                'last_page' => max(1, (int) ceil($total / $perPage)),
                'per_page' => $perPage,
                'total' => $total,
                'points_balance' => $leaderboard->pointsForUser($request->user()->id),
                'scoring' => 'Points equal each quiz’s best passed score; only improvements to a quiz best add points.',
            ],
        ]);
    }

    public function language(Request $request)
    {
        return response()->json(['data' => [
            'locale' => $request->user()->locale ?: 'en',
            'supported_locales' => [
                ['code' => 'en', 'name' => 'English'],
                ['code' => 'sw', 'name' => 'Kiswahili'],
            ],
        ]]);
    }

    public function updateLanguage(Request $request)
    {
        $validated = $request->validate([
            'locale' => ['required', 'string', Rule::in(['en', 'sw'])],
        ]);

        $user = $request->user();
        $user->forceFill(['locale' => $validated['locale']])->save();
        app()->setLocale($validated['locale']);

        return response()->json([
            'message' => __('Language preference updated successfully.'),
            'data' => [
                'locale' => $user->locale,
                'supported_locales' => [
                    ['code' => 'en', 'name' => 'English'],
                    ['code' => 'sw', 'name' => 'Kiswahili'],
                ],
            ],
        ]);
    }

    public function notifications(Request $request)
    {
        $filters = $request->validate([
            'filter' => ['nullable', Rule::in(['all', 'unread', 'read'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);
        $query = ReaderNotification::query()->where('user_id', $request->user()->id)->latest();

        if (($filters['filter'] ?? null) === 'unread') {
            $query->whereNull('read_at');
        } elseif (($filters['filter'] ?? null) === 'read') {
            $query->whereNotNull('read_at');
        }

        $notifications = $query->paginate($filters['per_page'] ?? 20);

        return response()->json([
            'data' => $notifications->getCollection()->map(fn (ReaderNotification $notification) => $this->notificationData($notification))->values(),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
                'unread_count' => ReaderNotification::query()->where('user_id', $request->user()->id)->whereNull('read_at')->count(),
            ],
        ]);
    }

    public function markNotificationRead(Request $request, int $notification)
    {
        $item = ReaderNotification::query()->where('user_id', $request->user()->id)->findOrFail($notification);
        $item->update(['read_at' => $item->read_at ?? now()]);

        return response()->json(['message' => 'Notification marked as read.', 'data' => $this->notificationData($item->fresh())]);
    }

    public function markAllNotificationsRead(Request $request)
    {
        $updated = ReaderNotification::query()
            ->where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now(), 'updated_at' => now()]);

        return response()->json(['message' => 'Unread notifications marked as read.', 'data' => ['updated_count' => $updated]]);
    }

    public function deleteNotification(Request $request, int $notification)
    {
        ReaderNotification::query()->where('user_id', $request->user()->id)->findOrFail($notification)->delete();

        return response()->json(['message' => 'Notification deleted successfully.']);
    }

    public function notificationPreferences(Request $request)
    {
        $saved = ReaderNotificationPreference::query()
            ->where('user_id', $request->user()->id)
            ->get()
            ->keyBy('type');

        return response()->json(['data' => collect(ReaderNotificationPreference::TYPES)->map(fn (string $type) => [
            'type' => $type,
            'in_app_enabled' => $saved->has($type) ? (bool) $saved->get($type)->in_app_enabled : true,
        ])->values()]);
    }

    public function updateNotificationPreferences(Request $request)
    {
        $payload = $request->validate([
            'preferences' => ['required', 'array', 'min:1'],
            'preferences.*.type' => ['required', 'string', 'distinct', Rule::in(ReaderNotificationPreference::TYPES)],
            'preferences.*.in_app_enabled' => ['required', 'boolean'],
        ]);

        DB::transaction(function () use ($request, $payload) {
            foreach ($payload['preferences'] as $preference) {
                ReaderNotificationPreference::query()->updateOrCreate(
                    ['user_id' => $request->user()->id, 'type' => $preference['type']],
                    ['in_app_enabled' => $preference['in_app_enabled']],
                );
            }
        });

        return response()->json([
            'message' => 'Notification preferences updated successfully.',
            'data' => $this->notificationPreferences($request)->getData(true)['data'],
        ]);
    }

    public function registerPushToken(Request $request)
    {
        $payload = $request->validate([
            'token' => ['required', 'string', 'max:512'],
            'platform' => ['nullable', Rule::in(['android', 'ios'])],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        $device = PushDeviceToken::query()->updateOrCreate(
            ['token' => $payload['token']],
            [
                'user_id' => $request->user()->id,
                'platform' => $payload['platform'] ?? 'android',
                'device_name' => $payload['device_name'] ?? null,
                'last_used_at' => now(),
            ],
        );

        return response()->json([
            'message' => 'Push token registered successfully.',
            'data' => ['platform' => $device->platform, 'device_name' => $device->device_name],
        ], 201);
    }

    public function unregisterPushToken(Request $request)
    {
        $payload = $request->validate(['token' => ['required', 'string', 'max:512']]);
        $deleted = PushDeviceToken::query()
            ->where('user_id', $request->user()->id)
            ->where('token', $payload['token'])
            ->delete();

        return response()->json([
            'message' => $deleted ? 'Push token removed successfully.' : 'Push token was not registered to this account.',
        ], $deleted ? 200 : 404);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $username = $request->input('username');
        if (is_string($username)) {
            $request->merge(['username' => mb_strtolower(trim($username))]);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'username' => $user->isReader()
                ? ['sometimes', 'nullable', 'string', 'min:3', 'max:24', 'regex:/\A[a-z0-9_]+\z/', Rule::unique(User::class, 'username')->ignore($user->id)]
                : ['prohibited'],
            'email' => ['sometimes', 'required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'phone_number' => ['sometimes', 'nullable', 'string', 'max:20'],
            'locale' => ['sometimes', 'required', 'string', Rule::in(['en', 'sw'])],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();
        if (isset($validated['locale'])) {
            app()->setLocale($validated['locale']);
        }

        return response()->json([
            'message' => 'Profile updated successfully.',
            'data' => $this->userData($user->fresh()),
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        app(UserActivityRecorder::class)->record($user, 'signed_out', 'android_app', false);
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function uploadProfilePhoto(Request $request)
    {
        $request->validate([
            'profile_photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user = $request->user();
        $previousPath = $user->profile_photo_path;
        $path = $request->file('profile_photo')->store('profile-photos', 'public');
        abort_unless($path, 500, 'Unable to save the profile photo.');

        try {
            $user->update(['profile_photo_path' => $path]);
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($path);
            throw $exception;
        }

        if ($previousPath && $previousPath !== $path) {
            Storage::disk('public')->delete($previousPath);
        }

        return response()->json([
            'message' => 'Profile photo uploaded successfully.',
            'data' => $this->userData($user->fresh()),
        ]);
    }

    public function books(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'genre' => ['nullable', 'string', 'max:120'],
            'author' => ['nullable', 'string', 'max:120'],
            'publisher' => ['nullable', 'string', 'max:120'],
            'language' => ['nullable', 'string', 'max:12'],
            'publication_year' => ['nullable', 'integer', 'min:1000', 'max:'.(now()->year + 1)],
            'min_pages' => ['nullable', 'integer', 'min:1'],
            'max_pages' => ['nullable', 'integer', 'min:1', 'gte:min_pages'],
            'reading_status' => ['nullable', Rule::in(ReaderShelf::STATUSES)],
            'quiz_available' => ['nullable', 'in:true,false,1,0'],
            'featured' => ['nullable', 'in:true,false,1,0'],
            'min_rating' => ['nullable', 'numeric', 'min:1', 'max:5'],
            'sort' => ['nullable', Rule::in(['newest', 'title', 'highest_rated', 'popularity', 'most_completed', 'most_dueled'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $query = Book::query()
            ->withQuizPerformanceStats()
            ->where('status', 'published')
            ->with(['authors', 'genres', 'publisher'])
            ->withCount([
                'readerShelves as reader_count',
                'readerShelves as completed_count' => fn ($shelves) => $shelves->where('status', 'completed'),
                'duels',
            ])
            ->withAvg(['reviews as average_rating' => fn ($reviews) => $reviews->where('status', 'published')], 'rating');

        if (! empty($validated['q'])) {
            $term = $validated['q'];
            $query->where(function ($books) use ($term) {
                $books->where('title', 'like', '%'.$term.'%')
                    ->orWhere('description', 'like', '%'.$term.'%')
                    ->orWhere('isbn', 'like', '%'.$term.'%')
                    ->orWhereHas('authors', fn ($authors) => $authors->where('name', 'like', '%'.$term.'%'))
                    ->orWhereHas('publisher', fn ($publisher) => $publisher->where('name', 'like', '%'.$term.'%'));
            });
        }

        if (! empty($validated['genre'])) {
            $genre = $validated['genre'];
            $query->whereHas('genres', fn ($genres) => $genres->where('slug', $genre)->orWhere('name', $genre));
        }

        if (! empty($validated['author'])) {
            $query->whereHas('authors', fn ($authors) => $authors->where('name', 'like', '%'.$validated['author'].'%'));
        }

        if (! empty($validated['publisher'])) {
            $query->whereHas('publisher', fn ($publisher) => $publisher->where('name', 'like', '%'.$validated['publisher'].'%'));
        }

        foreach (['language', 'publication_year'] as $field) {
            if (isset($validated[$field])) {
                $query->where($field, $validated[$field]);
            }
        }

        if (isset($validated['min_pages'])) {
            $query->where('page_count', '>=', $validated['min_pages']);
        }

        if (isset($validated['max_pages'])) {
            $query->where('page_count', '<=', $validated['max_pages']);
        }

        if (! empty($validated['reading_status'])) {
            $query->whereHas('readerShelves', fn ($shelves) => $shelves
                ->where('user_id', $request->user()->id)
                ->where('status', $validated['reading_status']));
        }

        if (isset($validated['quiz_available'])) {
            $quizAvailable = filter_var($validated['quiz_available'], FILTER_VALIDATE_BOOLEAN);
            $query->{$quizAvailable ? 'whereHas' : 'whereDoesntHave'}('quizzes', fn ($quizzes) => $quizzes->where('status', 'published'));
        }

        if (isset($validated['featured'])) {
            $query->where('featured', filter_var($validated['featured'], FILTER_VALIDATE_BOOLEAN));
        }

        if (isset($validated['min_rating'])) {
            $query->whereRaw(
                '(SELECT AVG(book_reviews.rating) FROM book_reviews WHERE book_reviews.book_id = books.id AND book_reviews.status = ?) >= CAST(? AS REAL)',
                ['published', $validated['min_rating']],
            );
        }

        match ($validated['sort'] ?? 'newest') {
            'title' => $query->orderBy('title')->orderBy('id'),
            'highest_rated' => $query->orderByDesc('average_rating')->orderByDesc('reader_count')->orderBy('id'),
            'popularity' => $query->orderByDesc('reader_count')->orderByDesc('id'),
            'most_completed' => $query->orderByDesc('completed_count')->orderByDesc('id'),
            'most_dueled' => $query->orderByDesc('duels_count')->orderByDesc('id'),
            default => $query->latest('id'),
        };

        $books = $query->paginate($validated['per_page'] ?? 15);

        return response()->json([
            'data' => $books->getCollection()->map(fn (Book $book) => $this->bookSummary($book))->values(),
            'meta' => [
                'current_page' => $books->currentPage(),
                'last_page' => $books->lastPage(),
                'per_page' => $books->perPage(),
                'total' => $books->total(),
            ],
        ]);
    }

    public function showBook(Request $request, Book $book)
    {
        $book = Book::query()->withQuizPerformanceStats()->with(['authors', 'genres', 'publisher', 'quizzes' => function ($query) {
            $query->where('status', 'published')
                ->withPerformanceStats()
                ->with(['questions' => fn ($questions) => $questions->orderBy('sort_order')->with('answers')]);
        }])->whereKey($book->id)->firstOrFail();
        abort_unless($book->status === 'published', 404);

        $progress = ReadingProgress::query()
            ->where('user_id', $request->user()->id)
            ->where('book_id', $book->id)
            ->first();

        return response()->json([
            'data' => array_merge($this->bookSummary($book), [
                'page_count' => $book->page_count,
                'language' => $book->language,
                'isbn' => $book->isbn,
                'pdf_url' => $book->pdf_path ? route('api.books.content', ['book' => $book->id]) : null,
                'progress' => $progress ? $this->progressData($progress) : null,
                'shelf_status' => ReaderShelf::query()->where('user_id', $request->user()->id)->where('book_id', $book->id)->value('status'),
                'quizzes' => $book->quizzes->map(fn (Quiz $quiz) => $this->quizData($quiz, $request->user())),
            ]),
        ]);
    }

    public function readingProgress(Request $request)
    {
        $progress = ReadingProgress::query()
            ->where('user_id', $request->user()->id)
            ->whereHas('book', fn ($query) => $query->where('status', 'published'))
            ->with(['book' => fn ($book) => $book->withQuizPerformanceStats()->with(['authors', 'genres', 'publisher'])])
            ->orderByDesc('last_progress_at')
            ->get()
            ->map(fn (ReadingProgress $item) => $this->progressData($item));

        return response()->json(['data' => $progress]);
    }

    public function shelf(Request $request)
    {
        $items = ReaderShelf::query()
            ->where('user_id', $request->user()->id)
            ->whereHas('book', fn ($books) => $books->where('status', 'published'))
            ->with(['book' => fn ($book) => $book->withQuizPerformanceStats()->with(['authors', 'genres', 'publisher'])])
            ->latest()
            ->get()
            ->map(fn (ReaderShelf $item) => [
                'book' => $this->bookSummary($item->book),
                'status' => $item->status,
                'updated_at' => $item->updated_at,
            ]);

        return response()->json(['data' => $items]);
    }

    public function updateShelf(Request $request, Book $book, ReaderShelfService $shelfService)
    {
        $payload = $request->validate(['status' => ['required', Rule::in(ReaderShelf::STATUSES)]]);
        $item = $shelfService->setStatus($request->user(), $book, $payload['status']);

        return response()->json([
            'message' => 'Reading shelf updated successfully.',
            'data' => ['book_id' => $item->book_id, 'status' => $item->status, 'updated_at' => $item->updated_at],
        ]);
    }

    public function deleteShelf(Request $request, Book $book, ReaderShelfService $shelfService)
    {
        $shelfService->remove($request->user(), $book);

        return response()->json(['message' => 'Book removed from your shelf.']);
    }

    public function bookmarks(Request $request)
    {
        $items = Bookmark::query()
            ->where('user_id', $request->user()->id)
            ->with(['book' => fn ($book) => $book->withQuizPerformanceStats()->with(['authors', 'genres', 'publisher'])])
            ->orderBy('book_id')
            ->orderBy('page_number')
            ->get()
            ->map(fn (Bookmark $bookmark) => $this->bookmarkData($bookmark));

        return response()->json(['data' => $items]);
    }

    public function storeBookmark(Request $request, BookmarkService $bookmarks)
    {
        $bookId = $request->validate(['book_id' => ['required', 'integer', 'exists:books,id']])['book_id'];
        $book = Book::query()->whereKey($bookId)->where('status', 'published')->firstOrFail();
        $pageRules = ['required', 'integer', 'min:1'];
        if ($book->page_count) {
            $pageRules[] = 'max:'.$book->page_count;
        }
        $payload = $request->validate([
            'page_number' => $pageRules,
            'label' => ['nullable', 'string', 'max:255'],
        ]);
        $request->validate([
            'page_number' => [Rule::unique('bookmarks', 'page_number')->where('user_id', $request->user()->id)->where('book_id', $book->id)],
        ]);
        $bookmark = $bookmarks->create($request->user(), $book, (int) $payload['page_number'], $payload['label'] ?? null)->load('book.authors', 'book.genres', 'book.publisher');

        return response()->json(['message' => 'Bookmark saved successfully.', 'data' => $this->bookmarkData($bookmark)], 201);
    }

    public function updateBookmark(Request $request, Bookmark $bookmark, BookmarkService $bookmarks)
    {
        $bookmark->loadMissing('book');
        $pageRules = ['required', 'integer', 'min:1'];
        if ($bookmark->book?->page_count) {
            $pageRules[] = 'max:'.$bookmark->book->page_count;
        }
        $payload = $request->validate([
            'page_number' => $pageRules,
            'label' => ['nullable', 'string', 'max:255'],
        ]);
        $request->validate([
            'page_number' => [Rule::unique('bookmarks', 'page_number')->where('user_id', $request->user()->id)->where('book_id', $bookmark->book_id)->ignore($bookmark->id)],
        ]);
        $updated = $bookmarks->update($request->user(), $bookmark, (int) $payload['page_number'], $payload['label'] ?? null)
            ->load('book.authors', 'book.genres', 'book.publisher');

        return response()->json(['message' => 'Bookmark updated successfully.', 'data' => $this->bookmarkData($updated)]);
    }

    public function deleteBookmark(Request $request, Bookmark $bookmark, BookmarkService $bookmarks)
    {
        $bookmarks->delete($request->user(), $bookmark);

        return response()->json(['message' => 'Bookmark removed successfully.']);
    }

    public function syncProgress(Request $request, Book $book)
    {
        abort_unless($book->status === 'published', 404);

        $normalized = $request->all();
        $normalized['current_page'] = $normalized['current_page']
            ?? $normalized['page']
            ?? $normalized['page_number']
            ?? $normalized['pageNumber']
            ?? $normalized['last_page_read']
            ?? $normalized['lastPageRead']
            ?? $normalized['currentPage']
            ?? null;
        $progressRules = ['current_page' => ['required', 'integer', 'min:1']];
        if ($book->page_count) {
            $progressRules['current_page'][] = 'max:'.$book->page_count;
        }
        $payload = validator($normalized, $progressRules)->validate();

        $result = DB::transaction(function () use ($request, $book, $payload) {
            $progress = ReadingProgress::query()
                ->where('user_id', $request->user()->id)
                ->where('book_id', $book->id)
                ->lockForUpdate()
                ->first();

            if (! $progress) {
                $progress = ReadingProgress::create([
                    'user_id' => $request->user()->id,
                    'book_id' => $book->id,
                    'last_page_read' => 0,
                    'pages_read_total' => 0,
                ]);
            }

            $previousPage = (int) $progress->last_page_read;
            $currentPage = (int) $payload['current_page'];
            $pagesRead = max(0, $currentPage - $previousPage);
            $now = now();

            $progress->update([
                'last_page_read' => $currentPage,
                'pages_read_total' => (int) $progress->pages_read_total + $pagesRead,
                'last_opened_at' => $now,
                'last_progress_at' => $pagesRead > 0 ? $now : $progress->last_progress_at,
            ]);

            if ($pagesRead > 0) {
                $goals = ReadingGoal::query()
                    ->where('user_id', $request->user()->id)
                    ->where('goal_type', 'pages')
                    ->where('book_id', $book->id)
                    ->where('status', 'active')
                    ->whereDate('start_date', '<=', $now->toDateString())
                    ->whereDate('end_date', '>=', $now->toDateString())
                    ->get();

                foreach ($goals as $goal) {
                    $goal->current_value = min($goal->target_value, $goal->current_value + $pagesRead);
                    if ($goal->current_value >= $goal->target_value) {
                        $goal->status = 'achieved';
                    }
                    $goal->save();
                }
            }

            return $progress->fresh();
        });

        return response()->json([
            'message' => 'Reading progress synced successfully.',
            'data' => [
                'book_id' => $book->id,
                'current_page' => (int) $result->last_page_read,
                'pages_read_total' => (int) $result->pages_read_total,
                'last_progress_at' => $result->last_progress_at,
            ],
        ]);
    }

    public function bookContent(Request $request, Book $book, BookContentService $content)
    {
        abort_unless($request->user()->isReader(), 403);

        if ($request->query('format') === 'pdf') {
            return $content->stream($book);
        }

        return response()->json(['data' => $content->manifest($book)]);
    }

    public function bookContentPdf(Request $request, Book $book, BookContentService $content)
    {
        abort_unless($request->user()->isReader(), 403);

        return $content->stream($book);
    }

    public function goals(Request $request)
    {
        $goals = ReadingGoal::query()
            ->where('user_id', $request->user()->id)
            ->with('book:id,title,slug,cover_image')
            ->latest()
            ->get()
            ->map(fn (ReadingGoal $goal) => $this->goalData($goal));

        return response()->json(['data' => $goals]);
    }

    public function storeGoal(Request $request, ReaderShelfService $shelves)
    {
        $payload = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'goal_type' => ['required', Rule::in(['books', 'pages'])],
            'book_id' => ['nullable', 'required_if:goal_type,pages', 'integer', 'exists:books,id'],
            'target_value' => ['required', 'integer', 'min:1'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        if (($payload['goal_type'] ?? null) === 'pages') {
            $publishedBook = Book::query()->whereKey($payload['book_id'])->where('status', 'published')->exists();
            if (! $publishedBook) {
                throw ValidationException::withMessages(['book_id' => 'Select a published book for a pages goal.']);
            }
        }

        $goal = ReadingGoal::create([
            'user_id' => $request->user()->id,
            'book_id' => $payload['goal_type'] === 'pages' ? (int) $payload['book_id'] : null,
            'title' => $payload['title'],
            'goal_type' => $payload['goal_type'],
            'target_value' => (int) $payload['target_value'],
            'current_value' => 0,
            'start_date' => $payload['start_date'],
            'end_date' => $payload['end_date'],
            'status' => 'active',
        ]);
        $shelves->refreshBookGoals($request->user());
        $goal->refresh();

        return response()->json([
            'message' => 'Reading goal created successfully.',
            'data' => $this->goalData($goal->load('book')),
        ], 201);
    }

    public function deleteGoal(Request $request, ReadingGoal $goal)
    {
        abort_unless((int) $goal->user_id === (int) $request->user()->id, 404);
        $goal->delete();

        return response()->json(['message' => 'Reading goal deleted successfully.']);
    }

    public function updateGoal(Request $request, ReadingGoal $goal, ReaderShelfService $shelves)
    {
        abort_unless((int) $goal->user_id === (int) $request->user()->id, 404);
        $payload = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'goal_type' => ['required', Rule::in(['books', 'pages'])],
            'book_id' => ['nullable', 'required_if:goal_type,pages', 'integer', 'exists:books,id'],
            'target_value' => ['required', 'integer', 'min:1'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        if ($payload['goal_type'] === 'pages' && ! Book::whereKey($payload['book_id'])->where('status', 'published')->exists()) {
            throw ValidationException::withMessages(['book_id' => 'Select a published book for a pages goal.']);
        }

        $goal->update([
            'title' => $payload['title'],
            'goal_type' => $payload['goal_type'],
            'book_id' => $payload['goal_type'] === 'pages' ? (int) $payload['book_id'] : null,
            'target_value' => (int) $payload['target_value'],
            'current_value' => min((int) $goal->current_value, (int) $payload['target_value']),
            'start_date' => $payload['start_date'],
            'end_date' => $payload['end_date'],
            'status' => $goal->current_value >= $payload['target_value'] ? 'achieved' : 'active',
        ]);
        $shelves->refreshBookGoals($request->user());

        return response()->json([
            'message' => 'Reading goal updated successfully.',
            'data' => $this->goalData($goal->fresh('book')),
        ]);
    }

    public function lessons(Request $request)
    {
        $lessons = Lesson::query()
            ->where('user_id', $request->user()->id)
            ->with('book:id,title,slug')
            ->latest()
            ->get()
            ->map(fn (Lesson $lesson) => $this->lessonData($lesson));

        return response()->json(['data' => $lessons]);
    }

    public function storeLesson(Request $request, ReaderContentService $content)
    {
        $payload = $request->validate([
            'book_id' => ['required', 'integer', 'exists:books,id'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:20000'],
            'visibility' => ['required', 'in:private,followers,public'],
            'status' => ['required', 'in:draft,published'],
        ]);
        $lesson = $content->storeLesson($request->user(), $payload)->load('book:id,title,slug');

        return response()->json(['message' => 'Lesson saved successfully.', 'data' => $this->lessonData($lesson)], 201);
    }

    public function updateLesson(Request $request, Lesson $lesson, ReaderContentService $content)
    {
        $payload = $request->validate([
            'book_id' => ['required', 'integer', 'exists:books,id'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:20000'],
            'visibility' => ['required', 'in:private,followers,public'],
            'status' => ['required', 'in:draft,published'],
        ]);
        $updated = $content->updateLesson($request->user(), $lesson, $payload);

        return response()->json(['message' => 'Lesson updated successfully.', 'data' => $this->lessonData($updated)]);
    }

    public function deleteLesson(Request $request, Lesson $lesson, ReaderContentService $content)
    {
        $content->deleteLesson($request->user(), $lesson);

        return response()->json(['message' => 'Lesson deleted successfully.']);
    }

    public function recommendations(Request $request)
    {
        $items = Recommendation::query()
            ->where('user_id', $request->user()->id)
            ->with('book:id,title,slug,cover_image')
            ->latest()
            ->get()
            ->map(fn (Recommendation $recommendation) => $this->recommendationData($recommendation));

        return response()->json(['data' => $items]);
    }

    public function storeRecommendation(Request $request, ReaderContentService $content)
    {
        $payload = $request->validate([
            'book_id' => ['required', 'integer', 'exists:books,id'],
            'message' => ['required', 'string', 'max:5000'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'visibility' => ['required', 'in:private,followers,public'],
            'status' => ['required', 'in:draft,published'],
        ]);
        $recommendation = $content->storeRecommendation($request->user(), $payload)->load('book:id,title,slug,cover_image');

        return response()->json(['message' => 'Recommendation saved successfully.', 'data' => $this->recommendationData($recommendation)], 201);
    }

    public function updateRecommendation(Request $request, Recommendation $recommendation, ReaderContentService $content)
    {
        $payload = $request->validate([
            'book_id' => ['required', 'integer', 'exists:books,id'],
            'message' => ['required', 'string', 'max:5000'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'visibility' => ['required', 'in:private,followers,public'],
            'status' => ['required', 'in:draft,published'],
        ]);
        $updated = $content->updateRecommendation($request->user(), $recommendation, $payload);

        return response()->json(['message' => 'Recommendation updated successfully.', 'data' => $this->recommendationData($updated)]);
    }

    public function deleteRecommendation(Request $request, Recommendation $recommendation, ReaderContentService $content)
    {
        $content->deleteRecommendation($request->user(), $recommendation);

        return response()->json(['message' => 'Recommendation deleted successfully.']);
    }

    public function bookReviews(Request $request, Book $book)
    {
        abort_unless($book->status === 'published', 404);
        $reviews = BookReview::query()
            ->where('book_id', $book->id)
            ->where('status', 'published')
            ->with('user:id,name')
            ->latest()
            ->get();

        return response()->json([
            'data' => $reviews->map(fn (BookReview $review) => $this->reviewData($review)),
            'meta' => [
                'count' => $reviews->count(),
                'average_rating' => $reviews->isEmpty() ? null : round((float) $reviews->avg('rating'), 1),
            ],
        ]);
    }

    public function reviews(Request $request)
    {
        $items = BookReview::query()
            ->where('user_id', $request->user()->id)
            ->with(['book:id,title,slug', 'user:id,name'])
            ->latest()
            ->get()
            ->map(fn (BookReview $review) => $this->reviewData($review));

        return response()->json(['data' => $items]);
    }

    public function storeReview(Request $request, BookReviewService $reviews)
    {
        $payload = $request->validate([
            'book_id' => ['required', 'integer', 'exists:books,id', Rule::unique('book_reviews', 'book_id')->where('user_id', $request->user()->id)],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string', 'min:10', 'max:5000'],
        ]);
        $book = Book::query()->whereKey($payload['book_id'])->where('status', 'published')->firstOrFail();
        $review = $reviews->create($request->user(), $book, $payload)->load(['book', 'user']);

        return response()->json(['message' => 'Review submitted for moderation.', 'data' => $this->reviewData($review)], 201);
    }

    public function updateReview(Request $request, BookReview $review, BookReviewService $reviews)
    {
        $payload = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string', 'min:10', 'max:5000'],
        ]);
        $updated = $reviews->update($request->user(), $review, $payload);

        return response()->json(['message' => 'Review updated and returned to moderation.', 'data' => $this->reviewData($updated)]);
    }

    public function deleteReview(Request $request, BookReview $review, BookReviewService $reviews)
    {
        $reviews->delete($request->user(), $review);

        return response()->json(['message' => 'Review deleted successfully.']);
    }

    public function shows(Request $request)
    {
        $shows = LiveShow::query()
            ->where('status', 'scheduled')
            ->where('start_at', '>=', now())
            ->with('book:id,title,slug,cover_image')
            ->withCount('rsvps')
            ->withExists(['rsvps as is_registered' => fn ($rsvps) => $rsvps->where('user_id', $request->user()->id)])
            ->orderBy('start_at')
            ->get()
            ->map(fn (LiveShow $show) => $this->showData($show));

        return response()->json(['data' => $shows]);
    }

    public function showApplicationOptions(Request $request, LiveShow $show, ShowParticipationService $participation)
    {
        abort_unless($request->user()->isReader(), 403);

        $books = $participation->applicationOptions($request->user(), $show)
            ->map(fn (Book $book) => [
                'id' => $book->id,
                'title' => $book->title,
                'slug' => $book->slug,
                'cover_image_url' => $this->storageUrl($book->cover_image),
            ]);

        return response()->json(['data' => ['show_id' => $show->id, 'eligible_books' => $books]]);
    }

    public function showRsvp(Request $request, LiveShow $show, ShowParticipationService $participation, ReaderNotificationService $notifications)
    {
        $rsvp = $participation->rsvp($request->user(), $show);
        if ($rsvp->wasRecentlyCreated) {
            $notifications->sendTranslated($request->user(), 'show_rsvp', 'Show RSVP confirmed', 'You are registered for :show.', ['show' => $show->title], ['show_id' => $show->id]);
        }

        return response()->json([
            'message' => 'You are registered for this show.',
            'data' => ['id' => $rsvp->id, 'show_id' => $show->id, 'created_at' => $rsvp->created_at],
        ], 201);
    }

    public function cancelShowRsvp(Request $request, LiveShow $show, ShowParticipationService $participation)
    {
        $participation->cancelRsvp($request->user(), $show);

        return response()->json(['message' => 'Show RSVP cancelled successfully.']);
    }

    public function showApplications(Request $request)
    {
        $applications = ShowApplication::query()
            ->where('user_id', $request->user()->id)
            ->with(['show:id,title,start_at,status,book_id', 'book:id,title,slug'])
            ->latest()
            ->get()
            ->map(fn (ShowApplication $application) => $this->showApplicationData($application));

        return response()->json(['data' => $applications]);
    }

    public function applyToShow(Request $request, LiveShow $show, ShowParticipationService $participation)
    {
        abort_unless($request->user()->isReader(), 403);
        $payload = $request->validate([
            'book_id' => ['required', 'integer', 'exists:books,id'],
            'motivation' => ['required', 'string', 'min:20', 'max:2000'],
        ]);
        $application = $participation->apply($request->user(), $show, (int) $payload['book_id'], $payload['motivation']);

        return response()->json([
            'message' => __('Live show application submitted successfully.'),
            'data' => $this->showApplicationData($application),
        ], 201);
    }

    public function withdrawShowApplication(Request $request, ShowApplication $application, ShowParticipationService $participation)
    {
        $updated = $participation->withdraw($request->user(), $application);

        return response()->json([
            'message' => __('Live show application withdrawn successfully.'),
            'data' => $this->showApplicationData($updated),
        ]);
    }

    public function readers(Request $request, DuelService $duels)
    {
        abort_unless($request->user()->isReader(), 403);
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'book_id' => ['nullable', 'integer', 'exists:books,id'],
            'online_only' => ['nullable', 'boolean'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);
        $cutoff = now()->subMinutes(2);
        $query = User::query()->where('role', 'reader')->whereKeyNot($request->user()->id);

        if (! empty($filters['book_id'])) {
            $eligibleIds = $duels->verifiedOpponents($request->user(), Book::findOrFail($filters['book_id']))->modelKeys();
            $query->whereIn('id', $eligibleIds);
        }
        if (! empty($filters['online_only'])) {
            $query->where('last_seen_at', '>=', $cutoff);
        }
        if (! empty($filters['q'])) {
            $term = trim($filters['q']);
            $query->where(fn ($builder) => $builder->where('name', 'like', '%'.$term.'%')->orWhere('username', 'like', '%'.$term.'%'));
        }

        $readers = $query->orderByDesc('last_seen_at')->orderBy('name')
            ->paginate($filters['per_page'] ?? 20);

        return response()->json([
            'data' => $readers->getCollection()->map(fn (User $reader) => $this->readerDirectoryData($reader, $cutoff))->values(),
            'meta' => [
                'current_page' => $readers->currentPage(),
                'last_page' => $readers->lastPage(),
                'per_page' => $readers->perPage(),
                'total' => $readers->total(),
            ],
        ]);
    }

    public function likeReader(Request $request, User $reader, ReaderNotificationService $notifications)
    {
        abort_unless($request->user()->isReader(), 403);
        abort_unless($reader->isReader(), 404);
        if ((int) $reader->id === (int) $request->user()->id) {
            throw ValidationException::withMessages(['reader' => 'You cannot send a like request to yourself.']);
        }

        $likeRequest = ReaderLikeRequest::query()->firstOrNew([
            'sender_id' => $request->user()->id,
            'recipient_id' => $reader->id,
        ]);
        $created = ! $likeRequest->exists;
        $resent = $likeRequest->exists && $likeRequest->status === 'rejected';
        if ($created || $resent) {
            $likeRequest->status = 'pending';
            $likeRequest->save();
            $notifications->sendTranslated(
                $reader,
                'reader_like_request',
                'New reader like request',
                ':actor sent you a reader request.',
                ['actor' => $request->user()->username ?: $request->user()->name],
                ['like_request_id' => $likeRequest->id, 'reader_id' => $request->user()->id],
            );
        }

        return response()->json([
            'message' => $created || $resent ? 'Like request sent.' : 'Like request already exists.',
            'data' => $this->likeRequestData($likeRequest->load(['sender', 'recipient']), $request->user()),
        ], $created || $resent ? 201 : 200);
    }

    public function likeRequests(Request $request)
    {
        abort_unless($request->user()->isReader(), 403);
        $filters = $request->validate([
            'direction' => ['nullable', Rule::in(['received', 'sent', 'all'])],
            'status' => ['nullable', Rule::in(['pending', 'accepted', 'rejected'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);
        $direction = $filters['direction'] ?? 'all';
        $query = ReaderLikeRequest::query()->with(['sender:id,name,username,profile_photo_path,last_seen_at', 'recipient:id,name,username,profile_photo_path,last_seen_at']);
        if ($direction === 'received') {
            $query->where('recipient_id', $request->user()->id);
        } elseif ($direction === 'sent') {
            $query->where('sender_id', $request->user()->id);
        } else {
            $query->where(fn ($builder) => $builder->where('sender_id', $request->user()->id)->orWhere('recipient_id', $request->user()->id));
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        $items = $query->latest()->paginate($filters['per_page'] ?? 20);

        return response()->json([
            'data' => $items->getCollection()->map(fn (ReaderLikeRequest $item) => $this->likeRequestData($item, $request->user()))->values(),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
        ]);
    }

    public function respondToLikeRequest(Request $request, ReaderLikeRequest $likeRequest)
    {
        abort_unless($request->user()->isReader(), 403);
        abort_unless((int) $likeRequest->recipient_id === (int) $request->user()->id, 403);
        $payload = $request->validate(['action' => ['required', Rule::in(['accept', 'reject', 'decline'])]]);
        if ($likeRequest->status !== 'pending') {
            throw ValidationException::withMessages(['like_request' => 'Only pending requests can be answered.']);
        }
        $likeRequest->update(['status' => $payload['action'] === 'accept' ? 'accepted' : 'rejected']);

        return response()->json([
            'message' => 'Like request '.($payload['action'] === 'accept' ? 'accepted.' : 'declined.'),
            'data' => $this->likeRequestData($likeRequest->fresh(['sender', 'recipient']), $request->user()),
        ]);
    }

    public function leaderboard(Request $request, LeaderboardService $leaderboard)
    {
        $payload = $request->validate([
            'period' => ['nullable', Rule::in(LeaderboardService::PERIODS)],
            'type' => ['nullable', Rule::in(['reading', 'quizzes'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);
        $period = $payload['period'] ?? 'weekly';
        $type = $payload['type'] ?? 'quizzes';
        $readingRankings = $type === 'reading'
            ? $leaderboard->readingRankings($period, (int) ($payload['per_page'] ?? 20))
            : null;
        $rankings = $readingRankings?->getCollection() ?? $leaderboard->rankings($period);

        return response()->json([
            'data' => $rankings,
            'meta' => array_merge([
                'type' => $type,
                'period' => $period,
                'scoring' => $type === 'reading'
                    ? 'distinct books marked completed during the period; ties use book-goal completion rate, then achieved book-goal count'
                    : 'sum of each reader’s best passing score per published quiz',
            ], $readingRankings ? [
                'current_page' => $readingRankings->currentPage(),
                'last_page' => $readingRankings->lastPage(),
                'per_page' => $readingRankings->perPage(),
                'total' => $readingRankings->total(),
            ] : []),
        ]);
    }

    public function duels(Request $request, DuelService $duelService)
    {
        $user = $request->user();
        abort_unless($user->isReader(), 403);
        $verifiedBooks = $duelService->verifiedBooks($user);
        $duels = Duel::query()
            ->where(fn ($query) => $query->where('challenger_id', $user->id)->orWhere('opponent_id', $user->id))
            ->with(['book:id,title,slug', 'challenger:id,name,username,profile_photo_path,last_seen_at', 'opponent:id,name,username,profile_photo_path,last_seen_at', 'attempts:id,duel_id,quiz_id,user_id,score,submitted_at', 'attempts.quiz:id,title'])
            ->latest()
            ->get()
            ->map(fn (Duel $duel) => $this->duelData($duel, $user));

        $books = $verifiedBooks->map(fn (Book $book) => [
            'id' => $book->id,
            'title' => $book->title,
            'slug' => $book->slug,
            'opponents' => $duelService->verifiedOpponents($user, $book)->map(fn (User $opponent) => [
                'id' => $opponent->id,
                'name' => $opponent->name,
                'username' => $opponent->username,
                'profile_photo_url' => $this->storageUrl($opponent->profile_photo_path),
                'is_online' => $opponent->last_seen_at?->greaterThanOrEqualTo(now()->subMinutes(2)) ?? false,
                'last_seen_at' => $opponent->last_seen_at?->toIso8601String(),
            ])->values(),
        ])->values();

        return response()->json([
            'data' => $duels,
            'verified_books' => $books,
            'meta' => [
                'unlocked' => $verifiedBooks->isNotEmpty(),
                'verified_books_count' => $verifiedBooks->count(),
            ],
        ]);
    }

    public function showDuel(Request $request, Duel $duel)
    {
        $user = $request->user();
        abort_unless($user->isReader(), 403);
        abort_unless((int) $duel->challenger_id === (int) $user->id || (int) $duel->opponent_id === (int) $user->id, 404);
        $duel->load([
            'book:id,title,slug',
            'challenger:id,name,username,profile_photo_path,last_seen_at',
            'opponent:id,name,username,profile_photo_path,last_seen_at',
            'attempts:id,duel_id,quiz_id,user_id,score,submitted_at',
            'attempts.quiz:id,title',
        ]);
        $quizzes = Quiz::query()
            ->where('book_id', $duel->book_id)
            ->where('status', 'published')
            ->whereHas('questions')
            ->whereDoesntHave('questions', fn ($questions) => $questions->where('question_type', 'written_response'))
            ->whereHas('book', fn ($books) => $books->where('status', 'published'))
            ->orderBy('id')
            ->get(['id', 'title', 'instructions', 'duration_minutes']);

        return response()->json([
            'data' => $this->duelData($duel, $user) + [
                'available_quizzes' => $quizzes,
            ],
        ]);
    }

    public function submitDuelAnswers(Request $request, Duel $duel)
    {
        $user = $request->user();
        abort_unless($user->isReader(), 403);
        abort_unless((int) $duel->challenger_id === (int) $user->id || (int) $duel->opponent_id === (int) $user->id, 404);
        $payload = $request->validate([
            'quiz_id' => ['required', 'integer', 'exists:quizzes,id'],
            'answers' => ['required', 'array'],
        ]);
        $quiz = Quiz::query()
            ->with(['book', 'questions' => fn ($questions) => $questions->orderBy('sort_order')->with('answers')])
            ->where('book_id', $duel->book_id)
            ->where('status', 'published')
            ->whereHas('questions')
            ->whereDoesntHave('questions', fn ($questions) => $questions->where('question_type', 'written_response'))
            ->whereHas('book', fn ($books) => $books->where('status', 'published'))
            ->findOrFail($payload['quiz_id']);
        if ($quiz->questions->contains(fn ($question) => $question->question_type === 'written_response')) {
            throw ValidationException::withMessages(['quiz_id' => 'Written-response questions cannot be used in a duel.']);
        }
        $evaluation = QuizAnswerSelection::evaluate($payload['answers'], $quiz->questions);
        $totalPoints = (int) $quiz->questions->sum('points');
        $awardedPoints = collect($evaluation['responses'])->sum(fn (array $response) => (int) ($response['points_awarded'] ?? 0));
        $score = $totalPoints > 0 ? (int) round(($awardedPoints / $totalPoints) * 100) : 0;

        $updated = DB::transaction(function () use ($duel, $user, $quiz, $evaluation, $score) {
            $locked = Duel::query()->whereKey($duel->id)->lockForUpdate()->firstOrFail();
            abort_unless((int) $locked->challenger_id === (int) $user->id || (int) $locked->opponent_id === (int) $user->id, 404);
            if (! in_array($locked->status, ['accepted', 'live'], true)) {
                throw ValidationException::withMessages(['duel' => 'Answers can only be submitted after the invitation is accepted.']);
            }
            if ($locked->quiz_id && (int) $locked->quiz_id !== (int) $quiz->id) {
                throw ValidationException::withMessages(['quiz_id' => 'Both readers must answer the same quiz.']);
            }
            if (DuelAttempt::query()->where('duel_id', $locked->id)->where('user_id', $user->id)->exists()) {
                throw ValidationException::withMessages(['duel' => 'You have already submitted answers for this duel.']);
            }

            $locked->quiz_id ??= $quiz->id;
            DuelAttempt::create([
                'duel_id' => $locked->id,
                'quiz_id' => $quiz->id,
                'user_id' => $user->id,
                'score' => $score,
                'responses' => $evaluation['responses'],
                'submitted_at' => now(),
            ]);
            $attemptCount = DuelAttempt::query()->where('duel_id', $locked->id)->count();
            if ($attemptCount >= 2) {
                $locked->status = 'completed';
                $locked->completed_at = now();
            } else {
                $locked->status = 'live';
            }
            $locked->save();

            return $locked->fresh([
                'book:id,title,slug',
                'challenger:id,name,username,profile_photo_path,last_seen_at',
                'opponent:id,name,username,profile_photo_path,last_seen_at',
                'attempts:id,duel_id,quiz_id,user_id,score,submitted_at',
                'attempts.quiz:id,title',
            ]);
        });

        return response()->json([
            'message' => 'Duel answers submitted successfully.',
            'data' => $this->duelData($updated, $user),
        ]);
    }

    public function storeDuel(Request $request, DuelService $duelService, ReaderNotificationService $notifications)
    {
        $payload = $request->validate([
            'book_id' => ['required', 'integer', 'exists:books,id'],
            'opponent_id' => ['required', 'integer', 'exists:users,id'],
        ]);
        $duel = $duelService->challenge($request->user(), (int) $payload['opponent_id'], (int) $payload['book_id']);
        $duel->loadMissing(['book:id,title', 'opponent']);
        $notifications->sendTranslated($duel->opponent, 'duel_invitation', 'New duel invitation', ':actor challenged you to a duel for :book.', ['actor' => $request->user()->name, 'book' => $duel->book?->title], ['duel_id' => $duel->id, 'book_id' => $duel->book_id]);

        return response()->json([
            'message' => 'Duel invitation sent successfully.',
            'data' => $this->duelData($duel->load(['book', 'challenger', 'opponent']), $request->user()),
        ], 201);
    }

    public function respondToDuel(Request $request, Duel $duel, DuelService $duelService, ReaderNotificationService $notifications)
    {
        $payload = $request->validate(['action' => ['required', Rule::in(['accept', 'reject', 'decline'])]]);
        $action = $payload['action'] === 'decline' ? 'reject' : $payload['action'];
        $updated = $duelService->respond($request->user(), $duel, $action);
        $notifications->sendTranslated($updated->challenger, 'duel_response', 'Duel invitation :status', ':actor :status your duel invitation.', ['actor' => $request->user()->name, 'status' => __($updated->status, [], $updated->challenger->locale ?: 'en')], ['duel_id' => $updated->id, 'status' => $updated->status]);

        return response()->json(['message' => 'Duel invitation '.($action === 'accept' ? 'accepted.' : 'declined.'), 'data' => $this->duelData($updated, $request->user())]);
    }

    public function cancelDuel(Request $request, Duel $duel, DuelService $duelService)
    {
        $updated = $duelService->cancel($request->user(), $duel);

        return response()->json(['message' => 'Duel invitation cancelled.', 'data' => $this->duelData($updated, $request->user())]);
    }

    public function showQuiz(Request $request, Quiz $quiz)
    {
        $quiz = Quiz::query()->withPerformanceStats()->with([
            'book',
            'questions' => fn ($questions) => $questions->orderBy('sort_order')->with('answers'),
        ])->whereKey($quiz->id)->firstOrFail();
        abort_unless($quiz->status === 'published' && $quiz->book?->status === 'published', 404);

        return response()->json(['data' => $this->quizData($quiz, $request->user())]);
    }

    public function submitQuiz(Request $request, Quiz $quiz, ReaderNotificationService $notifications)
    {
        $quiz->load(['book', 'questions.answers']);
        abort_unless($quiz->status === 'published' && $quiz->book?->status === 'published', 404);

        $validated = $request->validate([
            'answers' => ['required', 'array'],
        ]);

        $evaluation = QuizAnswerSelection::evaluate($validated['answers'], $quiz->questions);

        $result = DB::transaction(function () use ($quiz, $request, $evaluation) {
            $lockedQuiz = Quiz::query()->whereKey($quiz->id)->lockForUpdate()->firstOrFail();
            $attemptsCount = QuizAttempt::query()
                ->where('quiz_id', $lockedQuiz->id)
                ->where('user_id', $request->user()->id)
                ->count();

            if ($attemptsCount >= $lockedQuiz->attempt_limit) {
                throw ValidationException::withMessages(['quiz' => 'Attempt limit reached for this quiz.']);
            }

            $score = 0;
            $totalPoints = 0;

            foreach ($quiz->questions as $question) {
                $totalPoints += (int) $question->points;
                $score += (int) ($evaluation['responses'][(int) $question->id]['points_awarded'] ?? 0);
            }

            $percentScore = $totalPoints > 0 ? (int) round(($score / $totalPoints) * 100) : 0;
            $reviewStatus = $evaluation['needs_review'] ? 'pending_review' : 'graded';
            $passed = ! $evaluation['needs_review'] && $percentScore >= (int) $lockedQuiz->pass_mark;
            $previousBest = (int) QuizAttempt::query()
                ->where('quiz_id', $lockedQuiz->id)
                ->where('user_id', $request->user()->id)
                ->where('review_status', 'graded')
                ->where('passed', true)
                ->max('score');

            $attempt = QuizAttempt::create([
                'quiz_id' => $lockedQuiz->id,
                'user_id' => $request->user()->id,
                'score' => $evaluation['needs_review'] ? 0 : $percentScore,
                'passed' => $passed,
                'review_status' => $reviewStatus,
            ]);

            foreach ($quiz->questions as $question) {
                $attempt->responses()->create([
                    'quiz_question_id' => $question->id,
                    ...$evaluation['responses'][(int) $question->id],
                ]);
            }

            return [
                'score' => $evaluation['needs_review'] ? null : $percentScore,
                'passed' => $evaluation['needs_review'] ? null : $passed,
                'review_status' => $reviewStatus,
                'attempt_id' => $attempt->id,
                'attempts_used' => $attemptsCount + 1,
                'reward_points' => $passed ? max(0, $percentScore - $previousBest) : 0,
            ];
        });

        $result['reward_balance'] = app(LeaderboardService::class)->pointsForUser($request->user()->id);
        $result['reward_label'] = 'Quiz leaderboard points';

        if ($result['review_status'] === 'graded') {
            $notifications->sendTranslated(
                $request->user(),
                $result['passed'] ? 'quiz_passed' : 'quiz_failed',
                $result['passed'] ? 'Quiz passed' : 'Quiz complete',
                'You scored :score% on :quiz.',
                ['score' => $result['score'], 'quiz' => $quiz->title],
                ['quiz_id' => $quiz->id, 'book_id' => $quiz->book_id, 'attempt_id' => $result['attempt_id'], 'score' => $result['score'], 'passed' => $result['passed']],
            );
        }

        return response()->json([
            'message' => $result['review_status'] === 'pending_review'
                ? 'Quiz submitted. Your written response is awaiting review.'
                : 'Quiz submitted successfully.',
            'data' => $result,
        ]);
    }

    private function userData(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'phone_number' => $user->phone_number,
            'role' => $user->role,
            'locale' => $user->locale ?: 'en',
            'profile_photo_url' => $this->storageUrl($user->profile_photo_path),
        ];
    }

    private function duelData(Duel $duel, ?User $viewer = null): array
    {
        $duel->loadMissing([
            'quiz:id,title',
            'attempts:id,duel_id,quiz_id,user_id,score,submitted_at',
            'attempts.quiz:id,title',
        ]);
        $attempts = $duel->attempts->keyBy('user_id');
        $challengerAttempt = $attempts->get($duel->challenger_id);
        $opponentAttempt = $attempts->get($duel->opponent_id);
        $winnerId = null;
        if ($challengerAttempt && $opponentAttempt && $challengerAttempt->score !== $opponentAttempt->score) {
            $winnerId = (int) ($challengerAttempt->score > $opponentAttempt->score ? $duel->challenger_id : $duel->opponent_id);
        }
        $viewerAttempt = $viewer ? $attempts->get($viewer->id) : null;
        $participant = $viewer && ((int) $viewer->id === (int) $duel->challenger_id || (int) $viewer->id === (int) $duel->opponent_id);

        return [
            'id' => $duel->id,
            'book' => $duel->book ? [
                'id' => $duel->book->id,
                'title' => $duel->book->title,
                'slug' => $duel->book->slug,
            ] : null,
            'challenger' => $duel->challenger ? [
                'id' => $duel->challenger->id,
                'name' => $duel->challenger->name,
                'username' => $duel->challenger->username,
                'profile_photo_url' => $this->storageUrl($duel->challenger->profile_photo_path),
                'is_online' => $duel->challenger->last_seen_at?->greaterThanOrEqualTo(now()->subMinutes(2)) ?? false,
                'last_seen_at' => $duel->challenger->last_seen_at?->toIso8601String(),
            ] : null,
            'opponent' => $duel->opponent ? [
                'id' => $duel->opponent->id,
                'name' => $duel->opponent->name,
                'username' => $duel->opponent->username,
                'profile_photo_url' => $this->storageUrl($duel->opponent->profile_photo_path),
                'is_online' => $duel->opponent->last_seen_at?->greaterThanOrEqualTo(now()->subMinutes(2)) ?? false,
                'last_seen_at' => $duel->opponent->last_seen_at?->toIso8601String(),
            ] : null,
            'quiz' => $duel->quiz ? ['id' => $duel->quiz->id, 'title' => $duel->quiz->title] : null,
            'status' => $duel->status,
            'completed_at' => $duel->completed_at?->toIso8601String(),
            'challenger_result' => $challengerAttempt ? [
                'submitted' => true,
                'score' => $duel->status === 'completed' || ($viewer && (int) $viewer->id === (int) $duel->challenger_id)
                    ? (int) $challengerAttempt->score
                    : null,
                'submitted_at' => $challengerAttempt->submitted_at?->toIso8601String(),
            ] : ['submitted' => false, 'score' => null, 'submitted_at' => null],
            'opponent_result' => $opponentAttempt ? [
                'submitted' => true,
                'score' => $duel->status === 'completed' || ($viewer && (int) $viewer->id === (int) $duel->opponent_id)
                    ? (int) $opponentAttempt->score
                    : null,
                'submitted_at' => $opponentAttempt->submitted_at?->toIso8601String(),
            ] : ['submitted' => false, 'score' => null, 'submitted_at' => null],
            'winner_id' => $winnerId,
            'my_result' => $viewerAttempt ? ['submitted' => true, 'score' => (int) $viewerAttempt->score] : ['submitted' => false, 'score' => null],
            'permissions' => [
                'can_accept' => $viewer && (int) $duel->opponent_id === (int) $viewer->id && $duel->status === 'pending',
                'can_decline' => $viewer && (int) $duel->opponent_id === (int) $viewer->id && $duel->status === 'pending',
                'can_cancel' => $viewer && (int) $duel->challenger_id === (int) $viewer->id && in_array($duel->status, ['pending', 'accepted', 'scheduled'], true),
                'can_submit_answers' => $participant && in_array($duel->status, ['accepted', 'live'], true) && ! $viewerAttempt,
            ],
            'created_at' => $duel->created_at,
        ];
    }

    private function readerDirectoryData(User $reader, ?Carbon $onlineCutoff = null): array
    {
        $onlineCutoff ??= now()->subMinutes(2);

        return [
            'id' => $reader->id,
            'name' => $reader->name,
            'username' => $reader->username,
            'profile_photo_url' => $this->storageUrl($reader->profile_photo_path),
            'is_online' => $reader->last_seen_at?->greaterThanOrEqualTo($onlineCutoff) ?? false,
            'last_seen_at' => $reader->last_seen_at?->toIso8601String(),
        ];
    }

    private function likeRequestData(ReaderLikeRequest $likeRequest, User $viewer): array
    {
        return [
            'id' => $likeRequest->id,
            'status' => $likeRequest->status,
            'sender' => $likeRequest->sender ? $this->readerDirectoryData($likeRequest->sender) : null,
            'recipient' => $likeRequest->recipient ? $this->readerDirectoryData($likeRequest->recipient) : null,
            'permissions' => [
                'can_respond' => (int) $likeRequest->recipient_id === (int) $viewer->id && $likeRequest->status === 'pending',
            ],
            'created_at' => $likeRequest->created_at?->toIso8601String(),
            'updated_at' => $likeRequest->updated_at?->toIso8601String(),
        ];
    }

    private function bookSummary(Book $book): array
    {
        return [
            'id' => $book->id,
            'title' => $book->title,
            'slug' => $book->slug,
            'description' => $book->description,
            'cover_image' => $book->cover_image,
            'cover_image_url' => $this->storageUrl($book->cover_image),
            'authors' => $book->authors->pluck('name')->values(),
            'genres' => $book->genres->pluck('name')->values(),
            'publisher' => $book->publisher?->name,
            'publication_year' => $book->publication_year,
            'page_count' => $book->page_count,
            'language' => $book->language,
            'featured' => (bool) $book->featured,
            'average_rating' => isset($book->average_rating) ? round((float) $book->average_rating, 2) : null,
            'reader_count' => (int) ($book->reader_count ?? 0),
            'completed_count' => (int) ($book->completed_count ?? 0),
            'duels_count' => (int) ($book->duels_count ?? 0),
            'published_quizzes_count' => (int) ($book->published_quizzes_count ?? 0),
            'quiz_readers_count' => (int) ($book->quiz_readers_count ?? 0),
            'quiz_attempts_count' => (int) ($book->quiz_attempts_count ?? 0),
            'quiz_pending_review_attempts_count' => (int) ($book->quiz_pending_review_attempts_count ?? 0),
            'quiz_graded_attempts_count' => (int) ($book->quiz_graded_attempts_count ?? 0),
            'quiz_passed_attempts_count' => (int) ($book->quiz_passed_attempts_count ?? 0),
            'quiz_average_score' => $book->quiz_average_score !== null ? round((float) $book->quiz_average_score, 1) : null,
            'quiz_pass_rate' => $book->quiz_pass_rate,
            'quiz_best_score' => $book->quiz_best_score !== null ? (int) $book->quiz_best_score : null,
            'status' => $book->status,
        ];
    }

    private function notificationData(ReaderNotification $notification): array
    {
        return [
            'id' => $notification->id,
            'type' => $notification->type,
            'title' => $notification->title,
            'body' => $notification->body,
            'data' => $notification->data,
            'read_at' => $notification->read_at,
            'created_at' => $notification->created_at,
        ];
    }

    private function progressData(ReadingProgress $progress): array
    {
        if ($progress->book_id && (! $progress->relationLoaded('book') || ! array_key_exists('published_quizzes_count', $progress->book?->getAttributes() ?? []))) {
            $progress->load(['book' => fn ($book) => $book->withQuizPerformanceStats()->with(['authors', 'genres', 'publisher'])]);
        }

        return [
            'book_id' => $progress->book_id,
            'book' => $progress->book ? $this->bookSummary($progress->book) : null,
            'current_page' => (int) $progress->last_page_read,
            'pages_read_total' => (int) $progress->pages_read_total,
            'last_opened_at' => $progress->last_opened_at,
            'last_progress_at' => $progress->last_progress_at,
        ];
    }

    private function bookmarkData(Bookmark $bookmark): array
    {
        if (! $bookmark->relationLoaded('book') || ! array_key_exists('published_quizzes_count', $bookmark->book?->getAttributes() ?? [])) {
            $bookmark->load(['book' => fn ($book) => $book->withQuizPerformanceStats()->with(['authors', 'genres', 'publisher'])]);
        }

        return [
            'id' => $bookmark->id,
            'book' => $bookmark->book ? $this->bookSummary($bookmark->book) : null,
            'page_number' => (int) $bookmark->page_number,
            'label' => $bookmark->label,
            'created_at' => $bookmark->created_at,
        ];
    }

    private function reviewData(BookReview $review): array
    {
        if (! $review->relationLoaded('book')) {
            $review->load('book:id,title,slug');
        }
        if (! $review->relationLoaded('user')) {
            $review->load('user:id,name');
        }

        return [
            'id' => $review->id,
            'book' => $review->book ? ['id' => $review->book->id, 'title' => $review->book->title, 'slug' => $review->book->slug] : null,
            'rating' => (int) $review->rating,
            'title' => $review->title,
            'body' => $review->body,
            'status' => $review->status,
            'reader' => $review->user ? ['id' => $review->user->id, 'name' => $review->user->name] : null,
            'created_at' => $review->created_at,
        ];
    }

    private function goalData(ReadingGoal $goal): array
    {
        return [
            'id' => $goal->id,
            'title' => $goal->title,
            'goal_type' => $goal->goal_type,
            'target_value' => (int) $goal->target_value,
            'current_value' => (int) $goal->current_value,
            'start_date' => Carbon::parse($goal->start_date)->toDateString(),
            'end_date' => Carbon::parse($goal->end_date)->toDateString(),
            'status' => $goal->status,
            'book' => $goal->book ? [
                'id' => $goal->book->id,
                'title' => $goal->book->title,
                'slug' => $goal->book->slug,
                'cover_image_url' => $this->storageUrl($goal->book->cover_image),
            ] : null,
        ];
    }

    private function lessonData(Lesson $lesson): array
    {
        if (! $lesson->relationLoaded('book')) {
            $lesson->load('book:id,title,slug');
        }

        return [
            'id' => $lesson->id,
            'title' => $lesson->title,
            'content' => $lesson->content,
            'visibility' => $lesson->visibility,
            'status' => $lesson->status,
            'book' => $lesson->book ? ['id' => $lesson->book->id, 'title' => $lesson->book->title, 'slug' => $lesson->book->slug] : null,
            'created_at' => $lesson->created_at,
        ];
    }

    private function showData(LiveShow $show): array
    {
        if (! $show->relationLoaded('book')) {
            $show->load('book:id,title,slug,cover_image');
        }

        return [
            'id' => $show->id,
            'title' => $show->title,
            'description' => $show->description,
            'start_at' => $show->start_at,
            'status' => $show->status,
            'rsvp_count' => (int) ($show->rsvps_count ?? 0),
            'is_registered' => (bool) ($show->is_registered ?? false),
            'book' => $show->book ? [
                'id' => $show->book->id,
                'title' => $show->book->title,
                'slug' => $show->book->slug,
                'cover_image_url' => $this->storageUrl($show->book->cover_image),
            ] : null,
        ];
    }

    private function showApplicationData(ShowApplication $application): array
    {
        if (! $application->relationLoaded('show')) {
            $application->load('show:id,title,start_at,status,book_id', 'book:id,title,slug');
        } elseif (! $application->relationLoaded('book')) {
            $application->load('book:id,title,slug');
        }

        return [
            'id' => $application->id,
            'show' => $application->show ? [
                'id' => $application->show->id,
                'title' => $application->show->title,
                'start_at' => $application->show->start_at,
                'status' => $application->show->status,
            ] : null,
            'book' => $application->book ? [
                'id' => $application->book->id,
                'title' => $application->book->title,
                'slug' => $application->book->slug,
            ] : null,
            'motivation' => $application->motivation,
            'quiz_score' => (int) $application->quiz_score,
            'status' => $application->status,
            'created_at' => $application->created_at,
        ];
    }

    private function recommendationData(Recommendation $recommendation): array
    {
        if (! $recommendation->relationLoaded('book')) {
            $recommendation->load('book:id,title,slug,cover_image');
        }

        return [
            'id' => $recommendation->id,
            'message' => $recommendation->message,
            'rating' => (int) $recommendation->rating,
            'visibility' => $recommendation->visibility,
            'status' => $recommendation->status,
            'book' => $recommendation->book ? [
                'id' => $recommendation->book->id,
                'title' => $recommendation->book->title,
                'slug' => $recommendation->book->slug,
                'cover_image_url' => $this->storageUrl($recommendation->book->cover_image),
            ] : null,
        ];
    }

    private function quizData(Quiz $quiz, User $user): array
    {
        $attempts = QuizAttempt::query()
            ->where('quiz_id', $quiz->id)
            ->where('user_id', $user->id);
        $gradedAttempts = (clone $attempts)->where('review_status', 'graded');
        $latestAttempt = (clone $attempts)->latest('id')->first();

        return [
            'id' => $quiz->id,
            'title' => $quiz->title,
            'instructions' => $quiz->instructions,
            'pass_mark' => (int) $quiz->pass_mark,
            'attempt_limit' => (int) $quiz->attempt_limit,
            'attempts_used' => (clone $attempts)->count(),
            'best_score' => (int) ($gradedAttempts->max('score') ?? 0),
            'latest_attempt' => $latestAttempt ? [
                'id' => $latestAttempt->id,
                'review_status' => $latestAttempt->review_status,
                'score' => $latestAttempt->review_status === 'graded' ? (int) $latestAttempt->score : null,
                'passed' => $latestAttempt->review_status === 'graded' ? (bool) $latestAttempt->passed : null,
                'submitted_at' => $latestAttempt->created_at,
            ] : null,
            'duration_minutes' => (int) $quiz->duration_minutes,
            'performance' => [
                'readers_count' => (int) ($quiz->readers_count ?? 0),
                'attempts_count' => (int) ($quiz->attempts_count ?? 0),
                'pending_review_attempts_count' => (int) ($quiz->pending_review_attempts_count ?? 0),
                'graded_attempts_count' => (int) ($quiz->graded_attempts_count ?? 0),
                'passed_attempts_count' => (int) ($quiz->passed_attempts_count ?? 0),
                'average_score' => $quiz->average_score !== null ? round((float) $quiz->average_score, 1) : null,
                'pass_rate' => $quiz->pass_rate,
                'best_score' => $quiz->best_score !== null ? (int) $quiz->best_score : null,
            ],
            'questions' => $quiz->questions->map(fn ($question) => [
                'id' => $question->id,
                'prompt' => $question->prompt,
                'question_type' => $question->question_type,
                'points' => (int) $question->points,
                'allow_multiple_selection' => $question->question_type === 'multiple_choice'
                    || $question->answers->where('is_correct', true)->count() > 1,
                'response_format' => match ($question->question_type) {
                    'one_word' => ['kind' => 'text', 'max_length' => 100, 'auto_graded' => true],
                    'short_answer' => ['kind' => 'textarea', 'max_length' => 1000, 'auto_graded' => true],
                    'written_response' => ['kind' => 'textarea', 'max_length' => 5000, 'auto_graded' => false, 'requires_review' => true],
                    default => ['kind' => 'choice'],
                },
                'answers' => in_array($question->question_type, ['single_choice', 'multiple_choice', 'true_false'], true)
                    || ($question->question_type === null && $question->answers->isNotEmpty())
                    ? $question->answers->map(fn ($answer) => [
                        'id' => $answer->id,
                        'body' => $answer->body,
                    ])->values()
                    : [],
            ])->values(),
        ];
    }

    private function storageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}
