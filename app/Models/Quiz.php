<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $fillable = ['book_id', 'title', 'instructions', 'pass_mark', 'attempt_limit', 'duration_minutes', 'status'];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function questions()
    {
        return $this->hasMany(QuizQuestion::class);
    }
}
