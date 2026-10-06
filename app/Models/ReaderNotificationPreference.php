<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReaderNotificationPreference extends Model
{
    public const TYPES = [
        'quiz_passed',
        'quiz_failed',
        'duel_invitation',
        'duel_response',
        'reader_like_request',
        'show_rsvp',
        'show_application_status',
        'goal_milestone',
    ];

    protected $fillable = ['user_id', 'type', 'in_app_enabled'];

    protected function casts(): array
    {
        return ['in_app_enabled' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
