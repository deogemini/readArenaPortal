<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'username', 'email', 'phone_number', 'password', 'email_verified_at', 'role', 'profile_photo_path', 'locale'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements HasLocalePreference
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['name', 'username', 'email', 'phone_number', 'password', 'email_verified_at', 'role', 'profile_photo_path', 'locale'];

    protected $hidden = ['password', 'remember_token'];

    public function preferredLocale(): string
    {
        return in_array($this->locale, ['en', 'sw'], true) ? $this->locale : 'en';
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isAuthor(): bool
    {
        return $this->role === 'author';
    }

    public function isReader(): bool
    {
        return $this->role === 'reader';
    }

    public function readingGoals(): HasMany
    {
        return $this->hasMany(ReadingGoal::class);
    }

    public function readerShelves(): HasMany
    {
        return $this->hasMany(ReaderShelf::class);
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    public function bookReviews(): HasMany
    {
        return $this->hasMany(BookReview::class);
    }

    public function readerIdeas(): HasMany
    {
        return $this->hasMany(ReaderIdea::class);
    }

    public function challengerDuels(): HasMany
    {
        return $this->hasMany(Duel::class, 'challenger_id');
    }

    public function opponentDuels(): HasMany
    {
        return $this->hasMany(Duel::class, 'opponent_id');
    }
}
