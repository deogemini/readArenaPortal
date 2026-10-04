<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShowRsvp extends Model
{
    protected $fillable = ['live_show_id', 'user_id'];

    public function show(): BelongsTo
    {
        return $this->belongsTo(LiveShow::class, 'live_show_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
