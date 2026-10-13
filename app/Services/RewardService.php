<?php

namespace App\Services;

use App\Models\PointTransaction;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RewardService
{
    public function awardAccountCreated(User $user): int
    {
        return $this->awardOnce(
            $user,
            (int) config('gamification.account_created_points', 50),
            'account_created',
            'account-created:'.$user->id,
            ['label' => 'Welcome to ReadArena'],
        );
    }

    public function awardDailyLogin(User $user): int
    {
        $date = now()->toDateString();

        return $this->awardOnce(
            $user,
            (int) config('gamification.daily_login_points', 5),
            'daily_login',
            'daily-login:'.$user->id.':'.$date,
            ['label' => 'Daily login reward', 'date' => $date],
        );
    }

    public function awardQuizAttempt(QuizAttempt $attempt, int $scoreImprovement = 0): int
    {
        return $this->awardQuizCompletion($attempt)
            + $this->awardQuizScoreImprovement($attempt, $scoreImprovement);
    }

    public function awardQuizCompletion(QuizAttempt $attempt): int
    {
        $attempt->loadMissing('quiz:id,title');
        $metadata = [
            'quiz_title' => $attempt->quiz?->title ?? 'Quiz',
            'score' => (int) $attempt->score,
        ];
        $baseReward = $this->awardOnce(
            $attempt->user,
            (int) config('gamification.quiz_completion_points', 10),
            'quiz_completion',
            'quiz-completion:'.$attempt->id,
            ['label' => 'Quiz completed'] + $metadata,
            $attempt->id,
        );

        return $baseReward;
    }

    public function awardQuizScoreImprovement(QuizAttempt $attempt, int $scoreImprovement): int
    {
        if ($scoreImprovement <= 0) {
            return 0;
        }

        $attempt->loadMissing('quiz:id,title');

        return $this->awardOnce(
            $attempt->user,
            $scoreImprovement,
            'quiz_score_improvement',
            'quiz-score:'.$attempt->id,
            [
                'label' => 'Quiz score improvement',
                'quiz_title' => $attempt->quiz?->title ?? 'Quiz',
                'score' => (int) $attempt->score,
            ],
            $attempt->id,
        );
    }

    public function penalizeQuizAbandonment(User $user, Quiz $quiz, string $sessionId): int
    {
        $penalty = max(0, (int) config('gamification.quiz_abandon_penalty_points', 10));
        if ($penalty === 0) {
            return 0;
        }

        return $this->deductOnce(
            $user,
            $penalty,
            'quiz_abandoned',
            'quiz-abandon:'.$user->id.':'.$quiz->id.':'.$sessionId,
            ['label' => 'Unfinished quiz', 'quiz_title' => $quiz->title],
        );
    }

    public function awardOnce(
        User $user,
        int $points,
        string $reason,
        string $sourceKey,
        array $metadata = [],
        ?int $quizAttemptId = null,
    ): int {
        if ($points <= 0) {
            return 0;
        }

        return DB::transaction(function () use ($user, $points, $reason, $sourceKey, $metadata, $quizAttemptId): int {
            User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $existing = PointTransaction::query()->where('source_key', $sourceKey)->first();
            if ($existing) {
                return max(0, (int) $existing->points);
            }

            PointTransaction::query()->create([
                'user_id' => $user->id,
                'quiz_attempt_id' => $quizAttemptId,
                'points' => $points,
                'reason' => $reason,
                'source_key' => $sourceKey,
                'metadata' => $metadata,
            ]);

            return $points;
        });
    }

    public function deductOnce(
        User $user,
        int $points,
        string $reason,
        string $sourceKey,
        array $metadata = [],
    ): int {
        if ($points <= 0) {
            return 0;
        }

        return DB::transaction(function () use ($user, $points, $reason, $sourceKey, $metadata): int {
            User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $existing = PointTransaction::query()->where('source_key', $sourceKey)->first();
            if ($existing) {
                return abs(min(0, (int) $existing->points));
            }

            $balance = app(LeaderboardService::class)->pointsForUser((int) $user->id);
            $deduction = min($points, max(0, $balance));

            PointTransaction::query()->create([
                'user_id' => $user->id,
                'points' => -$deduction,
                'reason' => $reason,
                'source_key' => $sourceKey,
                'metadata' => $metadata + ['points_lost' => $deduction],
            ]);

            return $deduction;
        });
    }

    public static function labelForReason(string $reason): string
    {
        return match ($reason) {
            'account_created' => 'Welcome to ReadArena',
            'daily_login' => 'Daily login reward',
            'quiz_completion' => 'Quiz completed',
            'quiz_score_improvement' => 'Quiz score improvement',
            'quiz_abandoned' => 'Unfinished quiz penalty',
            'legacy_quiz_score' => 'Quiz score',
            default => 'Reading reward',
        };
    }
}
