<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DuelAttempt extends Model
{
    protected $fillable = ['duel_id', 'quiz_id', 'user_id', 'score', 'responses', 'submitted_at'];

    protected function casts(): array
    {
        return ['responses' => 'array', 'submitted_at' => 'datetime', 'score' => 'integer'];
    }

    public function duel(): BelongsTo
    {
        return $this->belongsTo(Duel::class);
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
