<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReadingGoal extends Model
{
    protected $fillable = ['user_id', 'book_id', 'title', 'goal_type', 'target_value', 'current_value', 'start_date', 'end_date', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}
