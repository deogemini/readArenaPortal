<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Bookmark;
use App\Models\BookReview;
use App\Models\Lesson;
use App\Models\LiveShow;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use App\Models\ReadingGoal;
use App\Models\ReadingProgress;
use App\Models\ReaderShelf;
use App\Models\Recommendation;
use App\Models\ShowApplication;
use App\Services\ReaderContentService;
use App\Services\DuelService;
use App\Services\LeaderboardService;
use App\Services\ShowParticipationService;
use App\Services\BookContentService;
use App\Services\ReaderShelfService;
use App\Services\BookmarkService;
use App\Services\BookReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReaderController extends Controller
{
    public function dashboard()
    {
        $readerId = auth()->id();
        $completedQuizzesCount = QuizAttempt::query()
            ->where('user_id', $readerId)
            ->where('passed', true)
            ->whereHas('quiz', fn ($quiz) => $quiz->where('status', 'published')->whereHas('book', fn ($book) => $book->where('status', 'published')))
            ->distinct('quiz_id')
            ->count('quiz_id');
        $verifiedBooks = app(DuelService::class)->verifiedBooks(auth()->user());

        return view('reader.dashboard', [
            'books' => Book::with(['authors', 'genres'])
                ->withCount(['quizzes' => fn ($quizzes) => $quizzes->where('status', 'published')])
                ->where('status', 'published')->latest()->take(6)->get(),
            'goals' => ReadingGoal::where('user_id', $readerId)->latest()->take(3)->get(),
            'shows' => LiveShow::where('status', 'scheduled')->where('start_at', '>=', now())->orderBy('start_at')->take(3)->get(),
            'lessons' => Lesson::where('user_id', $readerId)->latest()->take(3)->get(),
            'recommendations' => Recommendation::where('user_id', $readerId)->latest()->take(3)->get(),
            'totalPoints' => app(LeaderboardService::class)->pointsForUser($readerId),
            'quizAttemptsCount' => QuizAttempt::where('user_id', $readerId)->count(),
            'completedQuizzesCount' => $completedQuizzesCount,
            'verifiedBooksCount' => $verifiedBooks->count(),
            'verifiedBookIds' => $verifiedBooks->modelKeys(),
            'duelsUnlocked' => $completedQuizzesCount > 0,
            'leaderboard' => app(LeaderboardService::class)->rankings('weekly', 3),
        ]);
    }

    public function library()
    {
        return view('reader.library', [
            'books' => Book::with(['authors', 'genres'])->where('status', 'published')->latest()->paginate(9),
            'shelves' => ReaderShelf::query()->where('user_id', auth()->id())->get()->keyBy('book_id'),
        ]);
    }

    public function book(string $slug)
    {
        $book = Book::with(['authors', 'genres', 'quizzes' => function ($query) {
            $query->where('status', 'published')->with(['questions.answers']);
        }])->where('slug', $slug)->where('status', 'published')->firstOrFail();

        $readerId = auth()->id();

        $progress = ReadingProgress::query()->firstOrCreate(
            [
                'user_id' => $readerId,
                'book_id' => $book->id,
            ],
            [
                'last_page_read' => 0,
                'pages_read_total' => 0,
            ]
        );

        $progress->update([
            'last_opened_at' => now(),
        ]);

        $quizStats = $book->quizzes->mapWithKeys(function (Quiz $quiz) use ($readerId) {
            $attempts = QuizAttempt::where('quiz_id', $quiz->id)->where('user_id', $readerId)->get();

            return [
                $quiz->id => [
                    'attempts' => $attempts->count(),
                    'best_score' => (int) ($attempts->max('score') ?? 0),
                ],
            ];
        });

        $pageGoals = ReadingGoal::query()
            ->where('user_id', $readerId)
            ->where('goal_type', 'pages')
            ->where('book_id', $book->id)
            ->latest()
            ->get();

        return view('reader.book', [
            'book' => $book,
            'quizStats' => $quizStats,
            'pageGoals' => $pageGoals,
            'progress' => $progress->fresh(),
            'shelf' => ReaderShelf::query()->where('user_id', $readerId)->where('book_id', $book->id)->first(),
            'bookmarks' => Bookmark::query()->where('user_id', $readerId)->where('book_id', $book->id)->orderBy('page_number')->get(),
            'myReview' => BookReview::query()->where('user_id', $readerId)->where('book_id', $book->id)->first(),
        ]);
    }

    public function storeReview(Request $request, string $slug, BookReviewService $reviews): RedirectResponse
    {
        $book = Book::query()->where('slug', $slug)->where('status', 'published')->firstOrFail();
        $payload = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string', 'min:10', 'max:5000'],
        ]);
        if (BookReview::query()->where('user_id', $request->user()->id)->where('book_id', $book->id)->exists()) {
            return back()->withInput()->withErrors(['review' => 'You already reviewed this book. Edit your existing review instead.']);
        }
        $reviews->create($request->user(), $book, $payload);

        return redirect()->route('reader.books.show', $book->slug)->with('status', 'Review submitted and awaiting moderation.');
    }

    public function updateReview(Request $request, BookReview $review, BookReviewService $reviews): RedirectResponse
    {
        $payload = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string', 'min:10', 'max:5000'],
        ]);
        $updated = $reviews->update($request->user(), $review, $payload);

        return redirect()->route('reader.books.show', $updated->book->slug)->with('status', 'Review updated and returned to moderation.');
    }

    public function destroyReview(Request $request, BookReview $review, BookReviewService $reviews): RedirectResponse
    {
        $slug = $review->book->slug;
        $reviews->delete($request->user(), $review);

        return redirect()->route('reader.books.show', $slug)->with('status', 'Review deleted.');
    }

    public function storeBookmark(Request $request, string $slug, BookmarkService $bookmarks): RedirectResponse
    {
        $book = Book::query()->where('slug', $slug)->where('status', 'published')->firstOrFail();
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

        $bookmarks->create($request->user(), $book, (int) $payload['page_number'], $payload['label'] ?? null);

        return redirect()->route('reader.books.show', $book->slug)->with('status', 'Bookmark saved.');
    }

    public function updateBookmark(Request $request, Bookmark $bookmark, BookmarkService $bookmarks): RedirectResponse
    {
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
        $bookmarks->update($request->user(), $bookmark, (int) $payload['page_number'], $payload['label'] ?? null);

        return redirect()->route('reader.books.show', $bookmark->book->slug)->with('status', 'Bookmark updated.');
    }

    public function destroyBookmark(Request $request, Bookmark $bookmark, BookmarkService $bookmarks): RedirectResponse
    {
        $slug = $bookmark->book->slug;
        $bookmarks->delete($request->user(), $bookmark);

        return redirect()->route('reader.books.show', $slug)->with('status', 'Bookmark removed.');
    }

    public function updateShelf(Request $request, string $slug, ReaderShelfService $shelfService): RedirectResponse
    {
        $payload = $request->validate(['status' => ['required', 'in:want_to_read,currently_reading,completed,paused,abandoned']]);
        $book = Book::query()->where('slug', $slug)->where('status', 'published')->firstOrFail();
        $shelfService->setStatus($request->user(), $book, $payload['status']);

        return redirect()->route('reader.books.show', $book->slug)->with('status', 'Reading shelf updated.');
    }

    public function destroyShelf(Request $request, string $slug, ReaderShelfService $shelfService): RedirectResponse
    {
        $book = Book::query()->where('slug', $slug)->where('status', 'published')->firstOrFail();
        $shelfService->remove($request->user(), $book);

        return redirect()->route('reader.books.show', $book->slug)->with('status', 'Book removed from your shelf.');
    }

    public function streamBookContent(string $slug, BookContentService $content)
    {
        $book = Book::query()->where('slug', $slug)->where('status', 'published')->firstOrFail();

        return $content->stream($book);
    }

    public function trackPagesRead(Request $request, string $slug): RedirectResponse
    {
        $book = Book::where('slug', $slug)->where('status', 'published')->firstOrFail();

        $payload = $request->validate([
            'current_page' => ['required', 'integer', 'min:1'],
        ]);

        $currentPage = (int) $payload['current_page'];
        $now = now()->toDateString();

        $progress = ReadingProgress::query()->firstOrCreate(
            [
                'user_id' => auth()->id(),
                'book_id' => $book->id,
            ],
            [
                'last_page_read' => 0,
                'pages_read_total' => 0,
            ]
        );

        $lastPage = (int) $progress->last_page_read;
        $pagesRead = max(0, $currentPage - $lastPage);

        if ($pagesRead === 0) {
            return redirect()->route('reader.books.show', $book->slug)
                ->with('status', 'Book opened today, but no new pages were added. Continue from page '.max(1, $lastPage + 1).'.');
        }

        $progress->update([
            'last_page_read' => $currentPage,
            'pages_read_total' => (int) $progress->pages_read_total + $pagesRead,
            'last_progress_at' => now(),
        ]);

        $goals = ReadingGoal::query()
            ->where('user_id', auth()->id())
            ->where('goal_type', 'pages')
            ->where('book_id', $book->id)
            ->where('status', 'active')
            ->whereDate('start_date', '<=', $now)
            ->whereDate('end_date', '>=', $now)
            ->get();

        $achievedCount = 0;

        foreach ($goals as $goal) {
            $goal->current_value = min($goal->target_value, $goal->current_value + $pagesRead);

            if ($goal->current_value >= $goal->target_value) {
                $goal->status = 'achieved';
                $achievedCount++;
            }

            $goal->save();
        }

        $message = 'Great progress! You moved from page '.$lastPage.' to page '.$currentPage.' ('.$pagesRead.' new pages).';
        if ($achievedCount > 0) {
            $message .= ' Goal achieved!';
        }

        if ($goals->isEmpty()) {
            $message .= ' No active page goal is linked to this book yet.';
        }

        return redirect()->route('reader.books.show', $book->slug)->with('status', $message);
    }

    public function submitQuiz(Request $request, Quiz $quiz): RedirectResponse
    {
        if ($quiz->status !== 'published') {
            abort(404);
        }

        $quiz->load(['book', 'questions.answers']);

        if (!$quiz->book || $quiz->book->status !== 'published') {
            abort(404);
        }

        $readerId = auth()->id();
        $attemptsCount = QuizAttempt::where('quiz_id', $quiz->id)->where('user_id', $readerId)->count();

        if ($attemptsCount >= $quiz->attempt_limit) {
            return back()->withErrors([
                'quiz' => 'Attempt limit reached for this quiz.',
            ]);
        }

        $validated = $request->validate([
            'answers' => ['required', 'array'],
            'answers.*' => ['required', 'integer', 'exists:quiz_answers,id'],
        ]);

        $selectedAnswerIds = collect($validated['answers'])->map(fn ($value) => (int) $value);
        $answerMap = QuizAnswer::query()
            ->whereIn('id', $selectedAnswerIds)
            ->get()
            ->keyBy('id');

        $score = 0;
        $totalPoints = 0;

        foreach ($quiz->questions as $question) {
            $totalPoints += (int) $question->points;
            $answerId = (int) ($validated['answers'][$question->id] ?? 0);
            $answer = $answerMap->get($answerId);

            if ($answer && (int) $answer->quiz_question_id === (int) $question->id && $answer->is_correct) {
                $score += (int) $question->points;
            }
        }

        $percentScore = $totalPoints > 0 ? (int) round(($score / $totalPoints) * 100) : 0;

        QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'user_id' => $readerId,
            'score' => $percentScore,
            'passed' => $percentScore >= (int) $quiz->pass_mark,
        ]);

        return back()->with('status', 'Quiz submitted. You scored '.$percentScore.' points.');
    }

    public function goals()
    {
        return view('reader.goals', [
            'goals' => ReadingGoal::with('book')->where('user_id', auth()->id())->latest()->get(),
            'books' => Book::where('status', 'published')->orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function storeGoal(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'goal_type' => ['required', 'in:books,pages'],
            'book_id' => ['nullable', 'exists:books,id', 'required_if:goal_type,pages'],
            'target_value' => ['required', 'integer', 'min:1'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        if ($payload['goal_type'] === 'pages' && ! Book::whereKey($payload['book_id'])->where('status', 'published')->exists()) {
            return back()->withInput()->withErrors(['book_id' => 'Choose a published book for a pages goal.']);
        }

        ReadingGoal::create([
            'user_id' => auth()->id(),
            'book_id' => $payload['goal_type'] === 'pages' ? (int) $payload['book_id'] : null,
            'title' => $payload['title'],
            'goal_type' => $payload['goal_type'],
            'target_value' => (int) $payload['target_value'],
            'current_value' => 0,
            'start_date' => $payload['start_date'],
            'end_date' => $payload['end_date'],
            'status' => 'active',
        ]);

        return redirect()->route('reader.goals')->with('status', 'Reading goal created successfully.');
    }

    public function updateGoal(Request $request, ReadingGoal $goal): RedirectResponse
    {
        abort_unless((int) $goal->user_id === (int) $request->user()->id, 404);
        $payload = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'goal_type' => ['required', 'in:books,pages'],
            'book_id' => ['nullable', 'integer', 'exists:books,id', 'required_if:goal_type,pages'],
            'target_value' => ['required', 'integer', 'min:1'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        if ($payload['goal_type'] === 'pages' && ! Book::whereKey($payload['book_id'])->where('status', 'published')->exists()) {
            return back()->withInput()->withErrors(['book_id' => 'Choose a published book for a pages goal.']);
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

        return redirect()->route('reader.goals')->with('status', 'Reading goal updated successfully.');
    }

    public function destroyGoal(Request $request, ReadingGoal $goal): RedirectResponse
    {
        abort_unless((int) $goal->user_id === (int) $request->user()->id, 404);
        $goal->delete();

        return redirect()->route('reader.goals')->with('status', 'Reading goal deleted successfully.');
    }

    public function lessons()
    {
        return view('reader.lessons', [
            'lessons' => Lesson::with('book')->where('user_id', auth()->id())->latest()->get(),
            'books' => Book::where('status', 'published')->orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function storeLesson(Request $request, ReaderContentService $content): RedirectResponse
    {
        $payload = $request->validate([
            'book_id' => ['required', 'integer', 'exists:books,id'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:20000'],
            'visibility' => ['required', 'in:private,followers,public'],
            'status' => ['required', 'in:draft,published'],
        ]);

        $content->storeLesson($request->user(), $payload);

        return redirect()->route('reader.lessons')->with('status', 'Lesson saved successfully.');
    }

    public function updateLesson(Request $request, Lesson $lesson, ReaderContentService $content): RedirectResponse
    {
        $payload = $request->validate([
            'book_id' => ['required', 'integer', 'exists:books,id'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:20000'],
            'visibility' => ['required', 'in:private,followers,public'],
            'status' => ['required', 'in:draft,published'],
        ]);

        $content->updateLesson($request->user(), $lesson, $payload);

        return redirect()->route('reader.lessons')->with('status', 'Lesson updated successfully.');
    }

    public function destroyLesson(Request $request, Lesson $lesson, ReaderContentService $content): RedirectResponse
    {
        $content->deleteLesson($request->user(), $lesson);

        return redirect()->route('reader.lessons')->with('status', 'Lesson deleted successfully.');
    }

    public function recommendations()
    {
        return view('reader.recommendations', [
            'recommendations' => Recommendation::with('book')->where('user_id', auth()->id())->latest()->get(),
            'books' => Book::where('status', 'published')->orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function storeRecommendation(Request $request, ReaderContentService $content): RedirectResponse
    {
        $payload = $request->validate([
            'book_id' => ['required', 'integer', 'exists:books,id'],
            'message' => ['required', 'string', 'max:5000'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'visibility' => ['required', 'in:private,followers,public'],
            'status' => ['required', 'in:draft,published'],
        ]);

        $content->storeRecommendation($request->user(), $payload);

        return redirect()->route('reader.recommendations')->with('status', 'Recommendation saved successfully.');
    }

    public function updateRecommendation(Request $request, Recommendation $recommendation, ReaderContentService $content): RedirectResponse
    {
        $payload = $request->validate([
            'book_id' => ['required', 'integer', 'exists:books,id'],
            'message' => ['required', 'string', 'max:5000'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'visibility' => ['required', 'in:private,followers,public'],
            'status' => ['required', 'in:draft,published'],
        ]);

        $content->updateRecommendation($request->user(), $recommendation, $payload);

        return redirect()->route('reader.recommendations')->with('status', 'Recommendation updated successfully.');
    }

    public function destroyRecommendation(Request $request, Recommendation $recommendation, ReaderContentService $content): RedirectResponse
    {
        $content->deleteRecommendation($request->user(), $recommendation);

        return redirect()->route('reader.recommendations')->with('status', 'Recommendation deleted successfully.');
    }

    public function duels(DuelService $duels)
    {
        $user = auth()->user();
        $verifiedBooks = $duels->verifiedBooks($user);
        $opponentsByBook = $verifiedBooks->mapWithKeys(fn (Book $book) => [
            $book->id => $duels->verifiedOpponents($user, $book),
        ]);
        $records = \App\Models\Duel::query()
            ->where(fn ($query) => $query->where('challenger_id', $user->id)->orWhere('opponent_id', $user->id))
            ->with(['book:id,title,slug', 'challenger:id,name', 'opponent:id,name'])
            ->latest()
            ->paginate(10);

        return view('reader.duels', [
            'verifiedBooks' => $verifiedBooks,
            'opponentsByBook' => $opponentsByBook,
            'duels' => $records,
        ]);
    }

    public function storeDuel(Request $request, DuelService $duels): RedirectResponse
    {
        $payload = $request->validate([
            'book_id' => ['required', 'integer', 'exists:books,id'],
            'opponent_id' => ['required', 'integer', 'exists:users,id'],
        ]);
        $duels->challenge($request->user(), (int) $payload['opponent_id'], (int) $payload['book_id']);

        return redirect()->route('reader.duels')->with('status', 'Duel invitation sent.');
    }

    public function respondToDuel(Request $request, \App\Models\Duel $duel, DuelService $duels): RedirectResponse
    {
        $payload = $request->validate(['action' => ['required', 'in:accept,reject']]);
        $duels->respond($request->user(), $duel, $payload['action']);

        return redirect()->route('reader.duels')->with('status', 'Duel invitation '.$payload['action'].'ed.');
    }

    public function cancelDuel(Request $request, \App\Models\Duel $duel, DuelService $duels): RedirectResponse
    {
        $duels->cancel($request->user(), $duel);

        return redirect()->route('reader.duels')->with('status', 'Duel invitation cancelled.');
    }

    public function shows()
    {
        $readerId = auth()->id();
        $shows = LiveShow::query()
            ->where('status', 'scheduled')
            ->where('start_at', '>=', now())
            ->with('book')
            ->withCount(['rsvps', 'applications'])
            ->orderBy('start_at')
            ->get();
        $rsvpIds = \App\Models\ShowRsvp::query()->where('user_id', $readerId)->whereIn('live_show_id', $shows->pluck('id'))->pluck('live_show_id')->all();
        $applications = ShowApplication::query()
            ->where('user_id', $readerId)
            ->whereIn('live_show_id', $shows->pluck('id'))
            ->get()
            ->keyBy('live_show_id');

        return view('reader.shows', compact('shows', 'rsvpIds', 'applications'));
    }

    public function rsvpShow(Request $request, LiveShow $show, ShowParticipationService $participation): RedirectResponse
    {
        $participation->rsvp($request->user(), $show);

        return redirect()->route('reader.shows')->with('status', 'You are registered for this show.');
    }

    public function cancelShowRsvp(Request $request, LiveShow $show, ShowParticipationService $participation): RedirectResponse
    {
        $participation->cancelRsvp($request->user(), $show);

        return redirect()->route('reader.shows')->with('status', 'Show RSVP cancelled.');
    }

    public function applyToShow(Request $request, LiveShow $show, ShowParticipationService $participation): RedirectResponse
    {
        $payload = $request->validate(['motivation' => ['required', 'string', 'min:20', 'max:2000']]);
        $participation->apply($request->user(), $show, $payload['motivation']);

        return redirect()->route('reader.shows')->with('status', 'Guest application submitted.');
    }

    public function withdrawShowApplication(Request $request, ShowApplication $application, ShowParticipationService $participation): RedirectResponse
    {
        $participation->withdraw($request->user(), $application);

        return redirect()->route('reader.shows')->with('status', 'Guest application withdrawn.');
    }
}
