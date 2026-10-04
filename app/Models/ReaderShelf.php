<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReaderShelf extends Model
{
    public const STATUSES = ['want_to_read', 'currently_reading', 'completed', 'paused', 'abandoned'];

    protected $fillable = ['user_id', 'book_id', 'status'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}
