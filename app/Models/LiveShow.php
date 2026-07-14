<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiveShow extends Model
{
    protected $fillable = ['title', 'description', 'book_id', 'start_at', 'status'];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}
