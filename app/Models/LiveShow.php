<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiveShow extends Model
{
    protected $fillable = ['title', 'description', 'book_id', 'start_at', 'status'];

    protected $casts = [
        'start_at' => 'datetime',
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function rsvps()
    {
        return $this->hasMany(ShowRsvp::class);
    }

    public function applications()
    {
        return $this->hasMany(ShowApplication::class);
    }
}
