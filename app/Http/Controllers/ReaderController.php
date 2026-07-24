<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Lesson;
use App\Models\LiveShow;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use App\Models\ReadingGoal;
use App\Models\Recommendation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReaderController extends Controller
{
    public function dashboard()
    {
        $readerId = auth()->id();
        $requiredQuizzesForDuels = 10;
        $completedQuizzesCount = QuizAttempt::query()
            ->where('user_id', $readerId)
            ->distinct('quiz_id')
            ->count('quiz_id');

        return view('reader.dashboard', [
            'books' => Book::where('status', 'published')->latest()->take(6)->get(),
            'goals' => ReadingGoal::where('user_id', $readerId)->latest()->take(3)->get(),
            'shows' => LiveShow::where('status', 'scheduled')->latest()->take(3)->get(),
            'lessons' => Lesson::where('user_id', $readerId)->latest()->take(3)->get(),
            'recommendations' => Recommendation::where('user_id', $readerId)->latest()->take(3)->get(),
            'totalPoints' => (int) QuizAttempt::where('user_id', $readerId)->sum('score'),
            'quizAttemptsCount' => QuizAttempt::where('user_id', $readerId)->count(),
            'completedQuizzesCount' => $completedQuizzesCount,
            'requiredQuizzesForDuels' => $requiredQuizzesForDuels,
            'duelsUnlocked' => $completedQuizzesCount >= $requiredQuizzesForDuels,
        ]);
    }

    public function library()
    {
        return view('reader.library', [
            'books' => Book::where('status', 'published')->latest()->paginate(9),
        ]);
    }

    public function book(string $slug)
    {
        $book = Book::with(['authors', 'genres', 'quizzes' => function ($query) {
            $query->where('status', 'published')->with(['questions.answers']);
        }])->where('slug', $slug)->firstOrFail();

        $readerId = auth()->id();

        $quizStats = $book->quizzes->mapWithKeys(function (Quiz $quiz) use ($readerId) {
            $attempts = QuizAttempt::where('quiz_id', $quiz->id)->where('user_id', $readerId)->get();

            return [
                $quiz->id => [
                    'attempts' => $attempts->count(),
                    'best_score' => (int) ($attempts->max('score') ?? 0),
                ],
            ];
        });

        return view('reader.book', [
            'book' => $book,
            'quizStats' => $quizStats,
        ]);
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
        return view('reader.goals', ['goals' => ReadingGoal::where('user_id', auth()->id())->latest()->get()]);
    }

    public function storeGoal(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'goal_type' => ['required', 'in:books,pages'],
            'target_value' => ['required', 'integer', 'min:1'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        ReadingGoal::create([
            'user_id' => auth()->id(),
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

    public function lessons()
    {
        return view('reader.lessons', ['lessons' => Lesson::where('user_id', auth()->id())->latest()->get()]);
    }

    public function duels()
    {
        $readerId = auth()->id();
        $requiredQuizzesForDuels = 10;
        $completedQuizzesCount = QuizAttempt::query()
            ->where('user_id', $readerId)
            ->distinct('quiz_id')
            ->count('quiz_id');

        if ($completedQuizzesCount < $requiredQuizzesForDuels) {
            $remaining = $requiredQuizzesForDuels - $completedQuizzesCount;

            return redirect()->route('reader.dashboard')->with('warning', 'Complete '.$remaining.' more quizzes to unlock duels.');
        }

        return view('reader.duels');
    }

    public function shows()
    {
        return view('reader.shows', ['shows' => LiveShow::where('status', 'scheduled')->latest()->get()]);
    }
}
