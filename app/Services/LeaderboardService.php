<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LeaderboardService
{
    public const PERIODS = ['daily', 'weekly', 'monthly', 'all_time'];

    public function pointsForUser(int $userId): int
    {
        return (int) DB::table('quiz_attempts as attempts')
            ->join('quizzes', 'quizzes.id', '=', 'attempts.quiz_id')
            ->join('books', 'books.id', '=', 'quizzes.book_id')
            ->where('attempts.user_id', $userId)
            ->where('attempts.passed', true)
            ->where('quizzes.status', 'published')
            ->where('books.status', 'published')
            ->select('attempts.quiz_id')
            ->selectRaw('MAX(attempts.score) as best_score')
            ->groupBy('attempts.quiz_id')
            ->get()
            ->sum('best_score');
    }

    public function rankings(string $period = 'weekly', int $limit = 20): Collection
    {
        abort_unless(in_array($period, self::PERIODS, true), 422, 'Invalid leaderboard period.');

        $query = DB::table('quiz_attempts as attempts')
            ->join('quizzes', 'quizzes.id', '=', 'attempts.quiz_id')
            ->join('books', 'books.id', '=', 'quizzes.book_id')
            ->where('attempts.passed', true)
            ->where('quizzes.status', 'published')
            ->where('books.status', 'published');

        $since = match ($period) {
            'daily' => now()->startOfDay(),
            'weekly' => now()->startOfWeek(),
            'monthly' => now()->startOfMonth(),
            default => null,
        };

        if ($since) {
            $query->where('attempts.created_at', '>=', $since);
        }

        $bestScores = $query
            ->select('attempts.user_id', 'attempts.quiz_id')
            ->selectRaw('MAX(attempts.score) as best_score')
            ->groupBy('attempts.user_id', 'attempts.quiz_id')
            ->get()
            ->groupBy('user_id');

        if ($bestScores->isEmpty()) {
            return collect();
        }

        $users = User::query()
            ->whereIn('id', $bestScores->keys())
            ->where('role', 'reader')
            ->get(['id', 'name'])
            ->keyBy('id');

        return $bestScores
            ->map(function (Collection $quizzes, string $userId) use ($users) {
                $user = $users->get($userId);
                if (! $user) {
                    return null;
                }

                return [
                    'user_id' => (int) $user->id,
                    'name' => $user->name,
                    'points' => (int) $quizzes->sum('best_score'),
                    'verified_quizzes' => $quizzes->count(),
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
}
