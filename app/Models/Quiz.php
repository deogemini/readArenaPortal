<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class Quiz extends Model
{
    protected $fillable = ['book_id', 'title', 'instructions', 'pass_mark', 'attempt_limit', 'duration_minutes', 'status'];

    public function scopeWithPerformanceStats(Builder $query): Builder
    {
        $attempts = static fn () => DB::table('quiz_attempts')
            ->whereColumn('quiz_attempts.quiz_id', 'quizzes.id');

        return $query->addSelect([
            'attempts_count' => $attempts()->selectRaw('COUNT(*)'),
            'readers_count' => $attempts()->selectRaw('COUNT(DISTINCT quiz_attempts.user_id)'),
            'passed_attempts_count' => $attempts()->where('quiz_attempts.passed', true)->selectRaw('COUNT(*)'),
            'average_score' => $attempts()->selectRaw('AVG(quiz_attempts.score)'),
            'best_score' => $attempts()->selectRaw('MAX(quiz_attempts.score)'),
        ]);
    }

    public function getPassRateAttribute(): ?float
    {
        $attempts = (int) ($this->attempts_count ?? 0);

        return $attempts > 0
            ? round(((int) ($this->passed_attempts_count ?? 0) / $attempts) * 100, 1)
            : null;
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function questions()
    {
        return $this->hasMany(QuizQuestion::class);
    }

    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }
}
