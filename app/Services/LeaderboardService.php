<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LeaderboardService
{
    public const PERIODS = ['daily', 'weekly', 'monthly', 'all_time'];

    public function pointsForUser(int $userId): int
    {
        return (int) DB::table('point_transactions')
            ->where('user_id', $userId)
            ->sum('points');
    }

    public function rankings(string $period = 'weekly', int $limit = 20): Collection
    {
        abort_unless(in_array($period, self::PERIODS, true), 422, 'Invalid leaderboard period.');

        $since = match ($period) {
            'daily' => now()->startOfDay(),
            'weekly' => now()->startOfWeek(),
            'monthly' => now()->startOfMonth(),
            default => null,
        };

        $pointTotals = DB::table('point_transactions as points')
            ->join('users', 'users.id', '=', 'points.user_id')
            ->where('users.role', 'reader')
            ->when($since, fn ($query) => $query->where('points.created_at', '>=', $since))
            ->select('points.user_id')
            ->selectRaw('SUM(points.points) as total_points')
            ->groupBy('points.user_id')
            ->get()
            ->keyBy('user_id');

        if ($pointTotals->isEmpty()) {
            return collect();
        }

        $verifiedQuizzes = DB::table('quiz_attempts as attempts')
            ->join('quizzes', 'quizzes.id', '=', 'attempts.quiz_id')
            ->join('books', 'books.id', '=', 'quizzes.book_id')
            ->where('attempts.passed', true)
            ->where('attempts.review_status', 'graded')
            ->where('quizzes.status', 'published')
            ->where('books.status', 'published')
            ->when($since, fn ($query) => $query->where('attempts.created_at', '>=', $since))
            ->select('attempts.user_id')
            ->selectRaw('COUNT(DISTINCT attempts.quiz_id) as verified_quizzes')
            ->groupBy('attempts.user_id')
            ->get()
            ->keyBy('user_id');

        $users = User::query()
            ->whereIn('id', $pointTotals->keys())
            ->get(['id', 'name', 'profile_photo_path'])
            ->keyBy('id');

        return $pointTotals
            ->map(function (object $points, string $userId) use ($users, $verifiedQuizzes) {
                $user = $users->get($userId);
                if (! $user) {
                    return null;
                }

                return [
                    'user_id' => (int) $user->id,
                    'name' => $user->name,
                    'profile_photo_url' => $this->profilePhotoUrl($user->profile_photo_path),
                    'points' => (int) $points->total_points,
                    'verified_quizzes' => (int) ($verifiedQuizzes->get($userId)?->verified_quizzes ?? 0),
                ];
            })
            ->filter()
            ->sort(function (array $left, array $right) {
                return [$right['points'], $right['verified_quizzes'], mb_strtolower($left['name'])]
                    <=> [$left['points'], $left['verified_quizzes'], mb_strtolower($right['name'])];
            })
            ->values()
            ->take($limit)
            ->map(fn (array $rank, int $index) => ['rank' => $index + 1] + $rank);
    }

    public function readingRankings(string $period = 'weekly', int $perPage = 20): LengthAwarePaginator
    {
        abort_unless(in_array($period, self::PERIODS, true), 422, 'Invalid leaderboard period.');

        $since = match ($period) {
            'daily' => now()->startOfDay(),
            'weekly' => now()->startOfWeek(),
            'monthly' => now()->startOfMonth(),
            default => null,
        };

        $completedBooks = DB::table('reader_shelves')
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->when($since, fn ($query) => $query->where('completed_at', '>=', $since))
            ->select('user_id')
            ->selectRaw('COUNT(DISTINCT book_id) as completed_books')
            ->groupBy('user_id');

        $bookGoals = DB::table('reading_goals')
            ->where('goal_type', 'books')
            ->select('user_id')
            ->selectRaw('COUNT(*) as book_goals_count')
            ->selectRaw("SUM(CASE WHEN status = 'achieved' THEN 1 ELSE 0 END) as book_goals_achieved")
            ->selectRaw('COALESCE(SUM(current_value), 0) as books_goal_progress')
            ->selectRaw('COALESCE(SUM(target_value), 0) as books_goal_target')
            ->groupBy('user_id');

        $rankings = DB::table('users')
            ->where('users.role', 'reader')
            ->leftJoinSub($completedBooks, 'reading_totals', 'reading_totals.user_id', '=', 'users.id')
            ->leftJoinSub($bookGoals, 'goal_totals', 'goal_totals.user_id', '=', 'users.id')
            ->select('users.id as user_id', 'users.name', 'users.profile_photo_path')
            ->selectRaw('COALESCE(reading_totals.completed_books, 0) as completed_books')
            ->selectRaw('COALESCE(goal_totals.book_goals_count, 0) as book_goals_count')
            ->selectRaw('COALESCE(goal_totals.book_goals_achieved, 0) as book_goals_achieved')
            ->selectRaw('COALESCE(goal_totals.books_goal_progress, 0) as books_goal_progress')
            ->selectRaw('COALESCE(goal_totals.books_goal_target, 0) as books_goal_target')
            ->selectRaw('CASE WHEN COALESCE(goal_totals.books_goal_target, 0) > 0 THEN ROUND(goal_totals.books_goal_progress * 100.0 / goal_totals.books_goal_target, 1) ELSE 0 END as goal_completion_rate')
            ->orderByDesc('completed_books')
            ->orderByDesc('goal_completion_rate')
            ->orderByDesc('book_goals_achieved')
            ->orderBy('users.name')
            ->orderBy('users.id')
            ->paginate(max(1, min($perPage, 50)));

        $rankings->setCollection($rankings->getCollection()->values()->map(fn (object $reader, int $index): array => [
            'rank' => $rankings->firstItem() + $index,
            'user_id' => (int) $reader->user_id,
            'name' => $reader->name,
            'profile_photo_url' => $this->profilePhotoUrl($reader->profile_photo_path),
            'completed_books' => (int) $reader->completed_books,
            'book_goals_count' => (int) $reader->book_goals_count,
            'book_goals_achieved' => (int) $reader->book_goals_achieved,
            'books_goal_progress' => (int) $reader->books_goal_progress,
            'books_goal_target' => (int) $reader->books_goal_target,
            'goal_completion_rate' => (float) $reader->goal_completion_rate,
        ]));

        return $rankings;
    }

    private function profilePhotoUrl(?string $path): ?string
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
