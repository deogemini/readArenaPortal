<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookReview;
use App\Models\Duel;
use App\Models\Lesson;
use App\Models\LiveShow;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Recommendation;
use App\Models\SubscriptionPackage;
use App\Services\LeaderboardService;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function index()
    {
        $recentActivity = collect()
            ->merge(QuizAttempt::query()
                ->with(['quiz.book:id,title'])
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn (QuizAttempt $attempt) => [
                    'label' => $attempt->passed ? 'Quiz passed' : 'Quiz attempted',
                    'detail' => 'A reader '.($attempt->passed ? 'passed' : 'attempted').' a quiz for '.($attempt->quiz?->book?->title ?? 'a book'),
                    'date' => $attempt->created_at,
                ]))
            ->merge(Duel::query()
                ->with('book:id,title')
                ->latest('updated_at')
                ->limit(5)
                ->get()
                ->map(fn (Duel $duel) => [
                    'label' => 'Reading duel '.$duel->status,
                    'detail' => 'A reading duel was '.$duel->status.' for '.($duel->book?->title ?? 'a book'),
                    'date' => $duel->updated_at,
                ]))
            ->merge(LiveShow::query()
                ->where('status', 'scheduled')
                ->latest('updated_at')
                ->limit(5)
                ->get()
                ->map(fn (LiveShow $show) => [
                    'label' => 'Live show scheduled',
                    'detail' => $show->title.' starts '.$show->start_at?->format('M j, Y g:i A'),
                    'date' => $show->updated_at,
                ]))
            ->merge(Lesson::query()
                ->with('book:id,title')
                ->where('status', 'published')
                ->where('visibility', 'public')
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn (Lesson $lesson) => [
                    'label' => 'Lesson published',
                    'detail' => 'A reader shared a lesson for '.($lesson->book?->title ?? 'a book'),
                    'date' => $lesson->updated_at,
                ]))
            ->merge(Recommendation::query()
                ->with('book:id,title')
                ->where('status', 'published')
                ->where('visibility', 'public')
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn (Recommendation $recommendation) => [
                    'label' => 'Book recommended',
                    'detail' => 'A reader recommended '.($recommendation->book?->title ?? 'a book'),
                    'date' => $recommendation->updated_at,
                ]))
            ->sortByDesc('date')
            ->take(6)
            ->values();

        return view('public.home', [
            'books' => Book::where('status', 'published')->latest()->take(4)->get(),
            'shows' => LiveShow::where('status', 'scheduled')->latest()->take(3)->get(),
            'lessons' => Lesson::with(['book', 'user'])
                ->where('status', 'published')
                ->where('visibility', 'public')
                ->latest()
                ->take(3)
                ->get(),
            'quizzes' => Quiz::with('book')->where('status', 'published')->latest()->take(3)->get(),
            'recommendations' => Recommendation::with(['book', 'user'])
                ->where('status', 'published')
                ->where('visibility', 'public')
                ->latest()
                ->take(3)
                ->get(),
            'recentActivity' => $recentActivity,
        ]);
    }

    public function about()
    {
        return view('public.about');
    }

    public function library()
    {
        return view('public.library', [
            'books' => Book::query()->withQuizPerformanceStats()->with(['authors', 'genres'])
                ->where('status', 'published')->latest()->paginate(9),
        ]);
    }

    public function book(string $slug)
    {
        $book = Book::query()->withQuizPerformanceStats()->with(['authors', 'genres'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();
        $quizzes = Quiz::query()->withPerformanceStats()->withCount('questions')
            ->where('book_id', $book->id)
            ->where('status', 'published')
            ->orderBy('id')
            ->get();
        $recommendations = Recommendation::with('user')
            ->where('book_id', $book->id)
            ->where('status', 'published')
            ->where('visibility', 'public')
            ->latest()
            ->take(10)
            ->get();
        $reviews = BookReview::query()
            ->with('user:id,name')
            ->where('book_id', $book->id)
            ->where('status', 'published')
            ->latest()
            ->paginate(10);
        $reviewAverage = (float) BookReview::query()
            ->where('book_id', $book->id)
            ->where('status', 'published')
            ->avg('rating');

        return view('public.book', compact('book', 'quizzes', 'recommendations', 'reviews', 'reviewAverage'));
    }

    public function proArena()
    {
        return view('public.pro-arena', [
            'packages' => SubscriptionPackage::where('status', 'active')->orderBy('price_tsh')->get(),
        ]);
    }

    public function leaderboard(Request $request, LeaderboardService $leaderboard)
    {
        $payload = $request->validate([
            'period' => ['nullable', 'in:daily,weekly,monthly,all_time'],
            'type' => ['nullable', 'in:reading,quizzes'],
        ]);
        $period = $payload['period'] ?? 'weekly';
        $type = $payload['type'] ?? 'quizzes';

        return view('public.leaderboard', [
            'period' => $period,
            'type' => $type,
            'rankings' => $type === 'reading'
                ? $leaderboard->readingRankings($period)
                : $leaderboard->rankings($period),
        ]);
    }
}
