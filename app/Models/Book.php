<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class Book extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'publisher_id',
        'publication_year',
        'page_count',
        'language',
        'isbn',
        'cover_image',
        'pdf_path',
        'featured',
        'status',
    ];

    public function scopeWithQuizPerformanceStats(Builder $query): Builder
    {
        $publishedQuizIds = static fn () => DB::table('quizzes')
            ->select('quizzes.id')
            ->whereColumn('quizzes.book_id', 'books.id')
            ->where('quizzes.status', 'published');
        $attempts = static fn () => DB::table('quiz_attempts')
            ->join('users', 'users.id', '=', 'quiz_attempts.user_id')
            ->where('users.role', 'reader')
            ->whereIn('quiz_attempts.quiz_id', $publishedQuizIds());
        $gradedAttempts = static fn () => $attempts()->where('quiz_attempts.review_status', 'graded');

        return $query->addSelect([
            'published_quizzes_count' => DB::table('quizzes')
                ->selectRaw('COUNT(*)')
                ->whereColumn('quizzes.book_id', 'books.id')
                ->where('quizzes.status', 'published'),
            'quiz_attempts_count' => $attempts()->selectRaw('COUNT(*)'),
            'quiz_readers_count' => $attempts()->selectRaw('COUNT(DISTINCT quiz_attempts.user_id)'),
            'quiz_pending_review_attempts_count' => $attempts()->where('quiz_attempts.review_status', 'pending_review')->selectRaw('COUNT(*)'),
            'quiz_graded_attempts_count' => $gradedAttempts()->selectRaw('COUNT(*)'),
            'quiz_passed_attempts_count' => $gradedAttempts()->where('quiz_attempts.passed', true)->selectRaw('COUNT(*)'),
            'quiz_average_score' => $gradedAttempts()->selectRaw('AVG(quiz_attempts.score)'),
            'quiz_best_score' => $gradedAttempts()->selectRaw('MAX(quiz_attempts.score)'),
        ]);
    }

    public function getQuizPassRateAttribute(): ?float
    {
        $attempts = (int) ($this->quiz_graded_attempts_count ?? 0);

        return $attempts > 0
            ? round(((int) ($this->quiz_passed_attempts_count ?? 0) / $attempts) * 100, 1)
            : null;
    }

    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class);
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class);
    }

    public function publisher()
    {
        return $this->belongsTo(Publisher::class);
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }

    public function readerShelves()
    {
        return $this->hasMany(ReaderShelf::class);
    }

    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }

    public function reviews()
    {
        return $this->hasMany(BookReview::class);
    }

    public function duels()
    {
        return $this->hasMany(Duel::class);
    }
}
