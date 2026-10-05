<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    public const TYPES = [
        'single_choice',
        'multiple_choice',
        'true_false',
        'one_word',
        'short_answer',
        'written_response',
    ];

    protected $fillable = ['quiz_id', 'prompt', 'question_type', 'points', 'sort_order', 'last_edited_by', 'last_edited_at'];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function answers()
    {
        return $this->hasMany(QuizAnswer::class);
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'last_edited_by');
    }

    public function attemptResponses()
    {
        return $this->hasMany(QuizAttemptResponse::class);
    }
}
