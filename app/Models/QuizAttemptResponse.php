<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizAttemptResponse extends Model
{
    protected $fillable = [
        'quiz_attempt_id',
        'quiz_question_id',
        'answer_text',
        'selected_answer_ids',
        'points_awarded',
        'reviewed_at',
    ];

    protected $casts = [
        'selected_answer_ids' => 'array',
        'reviewed_at' => 'datetime',
        'points_awarded' => 'integer',
    ];

    public function attempt()
    {
        return $this->belongsTo(QuizAttempt::class, 'quiz_attempt_id');
    }

    public function question()
    {
        return $this->belongsTo(QuizQuestion::class, 'quiz_question_id');
    }
}
