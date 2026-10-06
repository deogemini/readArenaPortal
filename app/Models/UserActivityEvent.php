<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserActivityEvent extends Model
{
    protected $fillable = ['user_id', 'activity_key', 'platform'];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getActivityLabelAttribute(): string
    {
        return match ($this->activity_key) {
            'registered' => 'Created an account',
            'signed_in' => 'Signed in',
            'signed_out' => 'Signed out',
            'opened_dashboard' => 'Opened their dashboard',
            'browsed_library' => 'Browsed the library',
            'opened_book' => 'Opened a book',
            'opened_quiz' => 'Opened a quiz',
            'updated_reading_progress' => 'Updated reading progress',
            'saved_book' => 'Saved a book to their shelf',
            'added_bookmark' => 'Added a bookmark',
            'completed_quiz' => 'Submitted a quiz',
            'created_goal' => 'Created a reading goal',
            'shared_lesson' => 'Shared a reading lesson',
            'recommended_book' => 'Recommended a book',
            'reviewed_book' => 'Reviewed a book',
            'challenged_reader' => 'Sent a duel challenge',
            'responded_duel' => 'Responded to a duel',
            'submitted_duel_answers' => 'Submitted duel answers',
            'sent_reader_like' => 'Sent a reader request',
            'responded_like_request' => 'Responded to a reader request',
            'joined_show' => 'RSVP’d to a live show',
            'applied_show' => 'Applied to a live show',
            default => 'Used ReadArena',
        };
    }

    public function getPlatformLabelAttribute(): string
    {
        return $this->platform === 'android_app' ? 'Android app' : 'Web portal';
    }
}
