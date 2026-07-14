<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    protected $fillable = ['quiz_id', 'prompt', 'question_type', 'points', 'sort_order'];

    public function answers()
    {
        return $this->hasMany(QuizAnswer::class);
    }
}
