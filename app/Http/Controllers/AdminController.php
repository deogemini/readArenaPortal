<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Book;
use App\Models\Duel;
use App\Models\Genre;
use App\Models\Lesson;
use App\Models\LiveCompetitionVideo;
use App\Models\LiveShow;
use App\Models\PlatformSetting;
use App\Models\Publisher;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizQuestion;
use App\Models\Recommendation;
use App\Models\SubscriptionPackage;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $ongoingDuelStatuses = ['pending', 'ongoing', 'in_progress', 'live'];
        $ongoingShowStatuses = ['scheduled', 'ongoing', 'live'];
        $videoSearch = trim($request->string('video_search')->toString());

        $competitionVideosQuery = LiveCompetitionVideo::query()
            ->with('liveShow')
            ->latest();

        if ($videoSearch !== '') {
            $competitionVideosQuery->where('title', 'like', '%'.$videoSearch.'%');
        }

        return view('admin.dashboard', [
            'readers' => User::count(),
            'books' => Book::count(),
            'quizzes' => Quiz::count(),
            'shows' => LiveShow::count(),
            'lessons' => Lesson::count(),
            'recommendations' => Recommendation::count(),
            'ongoingCompetitions' => Duel::query()->whereIn('status', $ongoingDuelStatuses)->count()
                + LiveShow::query()
                    ->whereIn('status', $ongoingShowStatuses)
                    ->where('start_at', '<=', now())
                    ->count(),
            'liveShows' => LiveShow::query()->orderBy('start_at')->get(['id', 'title']),
            'competitionVideos' => $competitionVideosQuery->paginate(4)->withQueryString(),
            'videoSearch' => $videoSearch,
        ]);
    }

    public function storeCompetitionVideo(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'live_show_id' => ['nullable', 'exists:live_shows,id'],
            'video_file' => ['required', 'file', 'mimetypes:video/mp4,video/webm,video/quicktime', 'max:102400'],
            'status' => ['nullable', 'in:draft,published'],
        ]);

        $path = $request->file('video_file')->store('competition-videos', 'public');

        LiveCompetitionVideo::create([
            'title' => $payload['title'],
            'live_show_id' => $payload['live_show_id'] ?? null,
            'video_path' => $path,
            'uploaded_by' => $request->user()?->id,
            'status' => $payload['status'] ?? 'published',
        ]);

        return redirect()->route('admin.dashboard')->with('status', 'Competition video uploaded successfully.');
    }

    public function updateCompetitionVideo(Request $request, LiveCompetitionVideo $competitionVideo): RedirectResponse
    {
        $payload = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'live_show_id' => ['nullable', 'exists:live_shows,id'],
            'video_file' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm,video/quicktime', 'max:102400'],
            'status' => ['required', 'in:draft,published'],
        ]);

        $data = [
            'title' => $payload['title'],
            'live_show_id' => $payload['live_show_id'] ?? null,
            'status' => $payload['status'],
        ];

        if ($request->hasFile('video_file')) {
            if ($competitionVideo->video_path && Storage::disk('public')->exists($competitionVideo->video_path)) {
                Storage::disk('public')->delete($competitionVideo->video_path);
            }

            $data['video_path'] = $request->file('video_file')->store('competition-videos', 'public');
        }

        $competitionVideo->update($data);

        return redirect()->route('admin.dashboard')->with('status', 'Competition video updated successfully.');
    }

    public function destroyCompetitionVideo(LiveCompetitionVideo $competitionVideo): RedirectResponse
    {
        if ($competitionVideo->video_path && Storage::disk('public')->exists($competitionVideo->video_path)) {
            Storage::disk('public')->delete($competitionVideo->video_path);
        }

        $competitionVideo->delete();

        return redirect()->route('admin.dashboard')->with('status', 'Competition video deleted successfully.');
    }

    public function books()
    {
        return view('admin.books', [
            'books' => Book::with(['publisher', 'authors', 'genres'])->latest()->paginate(10),
        ]);
    }

    public function storeBook(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'author_name' => ['required', 'string', 'max:255'],
            'genre_name' => ['required', 'string', 'max:120'],
            'publisher_name' => ['nullable', 'string', 'max:255'],
            'publication_year' => ['nullable', 'integer', 'min:1400', 'max:2100'],
            'page_count' => ['nullable', 'integer', 'min:1'],
            'language' => ['nullable', 'string', 'max:10'],
            'isbn' => ['nullable', 'string', 'max:100'],
            'cover_image' => ['nullable', 'url'],
            'status' => ['nullable', 'in:draft,published'],
            'featured' => ['nullable', 'boolean'],
        ]);

        $publisher = Publisher::firstOrCreate([
            'name' => $payload['publisher_name'] ?? 'Independent Press',
        ]);

        $book = Book::create([
            'title' => $payload['title'],
            'slug' => Str::slug($payload['title']).'-'.Str::lower(Str::random(6)),
            'description' => $payload['description'] ?? null,
            'publisher_id' => $publisher->id,
            'publication_year' => $payload['publication_year'] ?? null,
            'page_count' => $payload['page_count'] ?? null,
            'language' => $payload['language'] ?? 'en',
            'isbn' => $payload['isbn'] ?? null,
            'cover_image' => $payload['cover_image'] ?? null,
            'featured' => (bool) ($payload['featured'] ?? false),
            'status' => $payload['status'] ?? 'draft',
        ]);

        $author = Author::firstOrCreate(['name' => $payload['author_name']]);
        $genre = Genre::firstOrCreate(
            ['slug' => Str::slug($payload['genre_name'])],
            ['name' => $payload['genre_name']]
        );

        $book->authors()->syncWithoutDetaching([$author->id]);
        $book->genres()->syncWithoutDetaching([$genre->id]);

        return redirect()->route('admin.books')->with('status', 'Book uploaded successfully.');
    }

    public function destroyBook(Book $book): RedirectResponse
    {
        $book->delete();

        return redirect()->route('admin.books')->with('status', 'Book deleted successfully.');
    }

    public function quizzes()
    {
        return view('admin.quizzes', [
            'quizzes' => Quiz::with(['book', 'questions.answers', 'questions.editor'])->withCount('attempts')->latest()->paginate(10),
            'books' => Book::orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function updateQuizQuestion(Request $request, QuizQuestion $question): RedirectResponse
    {
        $payload = $request->validate([
            'prompt' => ['required', 'string'],
            'points' => ['required', 'integer', 'min:1', 'max:100'],
            'sort_order' => ['required', 'integer', 'min:1'],
            'correct_answer' => ['required', 'string', 'max:1000'],
            'wrong_answer_1' => ['required', 'string', 'max:1000', 'different:correct_answer'],
            'wrong_answer_2' => ['required', 'string', 'max:1000', 'different:correct_answer', 'different:wrong_answer_1'],
        ]);

        $question->update([
            'prompt' => $payload['prompt'],
            'points' => (int) $payload['points'],
            'sort_order' => (int) $payload['sort_order'],
            'last_edited_by' => auth()->id(),
            'last_edited_at' => now(),
        ]);

        $question->load('answers');

        $correctAnswer = $question->answers->firstWhere('is_correct', true)
            ?? QuizAnswer::create([
                'quiz_question_id' => $question->id,
                'body' => $payload['correct_answer'],
                'is_correct' => true,
            ]);
        $correctAnswer->update(['body' => $payload['correct_answer'], 'is_correct' => true]);

        $wrongAnswers = $question->answers->where('is_correct', false)->values();

        $wrongOne = $wrongAnswers->get(0)
            ?? QuizAnswer::create([
                'quiz_question_id' => $question->id,
                'body' => $payload['wrong_answer_1'],
                'is_correct' => false,
            ]);
        $wrongOne->update(['body' => $payload['wrong_answer_1'], 'is_correct' => false]);

        $wrongTwo = $wrongAnswers->get(1)
            ?? QuizAnswer::create([
                'quiz_question_id' => $question->id,
                'body' => $payload['wrong_answer_2'],
                'is_correct' => false,
            ]);
        $wrongTwo->update(['body' => $payload['wrong_answer_2'], 'is_correct' => false]);

        return redirect()->route('admin.quizzes')->with('status', 'Question updated successfully.');
    }

    public function destroyQuiz(Quiz $quiz): RedirectResponse
    {
        if ($quiz->attempts()->exists()) {
            return redirect()->route('admin.quizzes')->withErrors([
                'quiz_delete' => 'This quiz already has reader attempts and cannot be deleted.',
            ]);
        }

        $quiz->delete();

        return redirect()->route('admin.quizzes')->with('status', 'Quiz deleted successfully.');
    }

    public function storeQuiz(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'book_id' => ['required', 'exists:books,id'],
            'title' => ['required', 'string', 'max:255'],
            'instructions' => ['nullable', 'string'],
            'pass_mark' => ['nullable', 'integer', 'min:1', 'max:100'],
            'attempt_limit' => ['nullable', 'integer', 'min:1'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'in:draft,published'],
        ]);

        Quiz::create([
            'book_id' => (int) $payload['book_id'],
            'title' => $payload['title'],
            'instructions' => $payload['instructions'] ?? null,
            'pass_mark' => $payload['pass_mark'] ?? 70,
            'attempt_limit' => $payload['attempt_limit'] ?? 3,
            'duration_minutes' => $payload['duration_minutes'] ?? 10,
            'status' => $payload['status'] ?? 'draft',
        ]);

        return redirect()->route('admin.quizzes')->with('status', 'Quiz created successfully.');
    }

    public function storeQuizQuestion(Request $request, Quiz $quiz): RedirectResponse
    {
        $payload = $request->validate([
            'prompt' => ['required', 'string'],
            'points' => ['nullable', 'integer', 'min:1', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:1'],
            'correct_answer' => ['required', 'string', 'max:1000'],
            'wrong_answer_1' => ['required', 'string', 'max:1000', 'different:correct_answer'],
            'wrong_answer_2' => ['required', 'string', 'max:1000', 'different:correct_answer', 'different:wrong_answer_1'],
        ]);

        $question = QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'prompt' => $payload['prompt'],
            'question_type' => 'multiple_choice',
            'points' => $payload['points'] ?? 10,
            'sort_order' => $payload['sort_order'] ?? ((int) $quiz->questions()->max('sort_order') + 1 ?: 1),
        ]);

        QuizAnswer::create([
            'quiz_question_id' => $question->id,
            'body' => $payload['correct_answer'],
            'is_correct' => true,
        ]);
        QuizAnswer::create([
            'quiz_question_id' => $question->id,
            'body' => $payload['wrong_answer_1'],
            'is_correct' => false,
        ]);
        QuizAnswer::create([
            'quiz_question_id' => $question->id,
            'body' => $payload['wrong_answer_2'],
            'is_correct' => false,
        ]);

        return redirect()->route('admin.quizzes')->with('status', 'Question added to quiz.');
    }

    public function duels()
    {
        return view('admin.duels');
    }

    public function shows()
    {
        return view('admin.shows', ['shows' => LiveShow::with('book')->latest()->paginate(10)]);
    }

    public function settings()
    {
        $roles = ['admin', 'author', 'reader'];
        $roleCounts = User::query()
            ->selectRaw('role, COUNT(*) as total')
            ->whereIn('role', $roles)
            ->groupBy('role')
            ->pluck('total', 'role');

        return view('admin.settings', [
            'settings' => PlatformSetting::orderBy('key')->get(),
            'roleSummaries' => collect($roles)->map(function (string $role) use ($roleCounts) {
                return [
                    'role' => $role,
                    'count' => (int) ($roleCounts[$role] ?? 0),
                ];
            }),
        ]);
    }

    public function users(Request $request)
    {
        $role = $request->string('role')->toString();
        $activity = $request->string('activity')->toString();
        $ongoingDuelStatuses = ['pending', 'ongoing', 'in_progress', 'live'];
        $query = User::query()
            ->withCount([
                'readingGoals as active_goals_count' => function (Builder $builder) {
                    $builder->where('status', 'active');
                },
                'challengerDuels as active_challenger_duels_count' => function (Builder $builder) use ($ongoingDuelStatuses) {
                    $builder->whereIn('status', $ongoingDuelStatuses);
                },
                'opponentDuels as active_opponent_duels_count' => function (Builder $builder) use ($ongoingDuelStatuses) {
                    $builder->whereIn('status', $ongoingDuelStatuses);
                },
            ])
            ->latest();

        if (in_array($role, ['reader', 'author', 'admin'], true)) {
            $query->where('role', $role);
        }

        if ($activity === 'active') {
            $query->where(function (Builder $builder) use ($ongoingDuelStatuses) {
                $builder
                    ->whereHas('readingGoals', function (Builder $goals) {
                        $goals->where('status', 'active');
                    })
                    ->orWhereHas('challengerDuels', function (Builder $duels) use ($ongoingDuelStatuses) {
                        $duels->whereIn('status', $ongoingDuelStatuses);
                    })
                    ->orWhereHas('opponentDuels', function (Builder $duels) use ($ongoingDuelStatuses) {
                        $duels->whereIn('status', $ongoingDuelStatuses);
                    });
            });
        }

        if ($activity === 'inactive') {
            $query
                ->whereDoesntHave('readingGoals', function (Builder $goals) {
                    $goals->where('status', 'active');
                })
                ->whereDoesntHave('challengerDuels', function (Builder $duels) use ($ongoingDuelStatuses) {
                    $duels->whereIn('status', $ongoingDuelStatuses);
                })
                ->whereDoesntHave('opponentDuels', function (Builder $duels) use ($ongoingDuelStatuses) {
                    $duels->whereIn('status', $ongoingDuelStatuses);
                });
        }

        $users = $query->paginate(20)->withQueryString();

        $users->getCollection()->transform(function (User $user) {
            $ongoingDuels = (int) $user->active_challenger_duels_count + (int) $user->active_opponent_duels_count;
            $user->ongoing_activities_count = (int) $user->active_goals_count + $ongoingDuels;

            return $user;
        });

        return view('admin.users', [
            'users' => $users,
            'selectedRole' => $role,
            'selectedActivity' => $activity,
        ]);
    }

    public function packages()
    {
        return view('admin.packages', [
            'packages' => SubscriptionPackage::latest()->paginate(20),
        ]);
    }

    public function storePackage(Request $request)
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:subscription_packages,name'],
            'price_tsh' => ['required', 'integer', 'min:1'],
            'games_count' => ['required', 'integer', 'min:1'],
            'reward_label' => ['nullable', 'string', 'max:255'],
            'region_scope' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);

        SubscriptionPackage::create([
            'name' => $payload['name'],
            'price_tsh' => $payload['price_tsh'],
            'games_count' => $payload['games_count'],
            'reward_label' => $payload['reward_label'] ?? null,
            'region_scope' => $payload['region_scope'] ?? 'global',
            'status' => $payload['status'] ?? 'active',
        ]);

        return redirect()->route('admin.packages')->with('status', 'Subscription package created.');
    }
}
