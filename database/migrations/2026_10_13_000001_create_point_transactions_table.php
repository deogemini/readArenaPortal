<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('point_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quiz_attempt_id')->nullable()->constrained()->cascadeOnDelete();
            $table->integer('points');
            $table->string('reason', 48);
            $table->string('source_key', 190)->unique();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
        });

        DB::table('users')
            ->where('role', 'reader')
            ->select('id')
            ->orderBy('id')
            ->chunkById(100, function ($users): void {
                $userIds = $users->pluck('id')->all();
                $attempts = DB::table('quiz_attempts as attempts')
                    ->join('quizzes', 'quizzes.id', '=', 'attempts.quiz_id')
                    ->join('books', 'books.id', '=', 'quizzes.book_id')
                    ->whereIn('attempts.user_id', $userIds)
                    ->where('attempts.passed', true)
                    ->where('attempts.review_status', 'graded')
                    ->where('quizzes.status', 'published')
                    ->where('books.status', 'published')
                    ->orderBy('attempts.user_id')
                    ->orderBy('attempts.quiz_id')
                    ->orderBy('attempts.id')
                    ->get([
                        'attempts.id',
                        'attempts.user_id',
                        'attempts.quiz_id',
                        'attempts.score',
                        'attempts.created_at',
                        'quizzes.title as quiz_title',
                    ]);

                $bestScores = [];
                $rows = [];
                foreach ($attempts as $attempt) {
                    $key = $attempt->user_id.':'.$attempt->quiz_id;
                    $previousBest = $bestScores[$key] ?? 0;
                    $improvement = max(0, (int) $attempt->score - $previousBest);
                    $bestScores[$key] = max($previousBest, (int) $attempt->score);

                    if ($improvement === 0) {
                        continue;
                    }

                    $timestamp = $attempt->created_at ?: now()->toDateTimeString();
                    $rows[] = [
                        'user_id' => $attempt->user_id,
                        'quiz_attempt_id' => $attempt->id,
                        'points' => $improvement,
                        'reason' => 'legacy_quiz_score',
                        'source_key' => 'legacy-quiz-score:'.$attempt->id,
                        'metadata' => json_encode([
                            'label' => 'Quiz score',
                            'quiz_title' => $attempt->quiz_title,
                            'score' => (int) $attempt->score,
                        ], JSON_THROW_ON_ERROR),
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ];
                }

                foreach (array_chunk($rows, 500) as $chunk) {
                    DB::table('point_transactions')->insert($chunk);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('point_transactions');
    }
};
