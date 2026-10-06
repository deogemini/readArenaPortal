<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserActivityEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserActivityRecorder
{
    public function touchPresence(User $user, string $platform): void
    {
        $now = now();

        DB::table('users')
            ->where('id', $user->id)
            ->where(function ($query) use ($now, $platform): void {
                $query->whereNull('last_seen_at')
                    ->orWhere('last_seen_at', '<=', $now->copy()->subSeconds(30))
                    ->orWhere('last_seen_platform', '!=', $platform);
            })
            ->update([
                'last_seen_at' => $now,
                'last_seen_platform' => $platform,
            ]);
    }

    public function record(User $user, string $activityKey, string $platform, bool $touchPresence = true): void
    {
        if ($touchPresence) {
            $this->touchPresence($user, $platform);
        }

        $event = UserActivityEvent::query()
            ->where('user_id', $user->id)
            ->where('activity_key', $activityKey)
            ->where('platform', $platform)
            ->where('created_at', '>=', now()->subSeconds(60));

        // Collapse repeated page refreshes while retaining every submitted action.
        if ($this->isViewEvent($activityKey) && $event->exists()) {
            return;
        }

        UserActivityEvent::create([
            'user_id' => $user->id,
            'activity_key' => $activityKey,
            'platform' => $platform,
        ]);
    }

    public function recordRequest(User $user, Request $request): void
    {
        $activityKey = $this->activityFor($request);
        $platform = $request->is('api/*') ? 'android_app' : 'web_portal';

        if ($activityKey !== null) {
            $this->record($user, $activityKey, $platform);
        } else {
            $this->touchPresence($user, $platform);
        }
    }

    private function activityFor(Request $request): ?string
    {
        $name = $request->route()?->getName() ?? '';
        $uri = strtolower(trim($request->route()?->uri() ?? $request->path(), '/'));
        $method = strtoupper($request->method());

        if ($method === 'GET') {
            return match (true) {
                in_array($name, ['reader.dashboard', 'author.dashboard']) || in_array($uri, ['api/dashboard', 'reader/dashboard', 'author/dashboard']) => 'opened_dashboard',
                $name === 'reader.library' || in_array($uri, ['api/books', 'reader/library']) => 'browsed_library',
                in_array($name, ['reader.books.show', 'reader.books.content']) || preg_match('#^(api/books/[^/]+(?:/content)?|reader/books/[^/]+(?:/content)?)$#', $uri) === 1 => 'opened_book',
                preg_match('#^api/quizzes/[^/]+$#', $uri) === 1 => 'opened_quiz',
                default => null,
            };
        }

        return match (true) {
            in_array($name, ['reader.quizzes.submit']) || preg_match('#^(api/)?quizzes/[^/]+/submit$#', $uri) === 1 => 'completed_quiz',
            in_array($name, ['reader.goals.store']) || ($method === 'POST' && in_array($uri, ['api/goals', 'reader/goals'])) => 'created_goal',
            in_array($name, ['reader.books.pages.track']) || preg_match('#^api/books/[^/]+/progress$#', $uri) === 1 => 'updated_reading_progress',
            in_array($name, ['reader.books.shelf.update', 'reader.books.shelf.store']) || preg_match('#^api/books/[^/]+/shelf$#', $uri) === 1 => 'saved_book',
            in_array($name, ['reader.books.bookmarks.store']) || $uri === 'api/bookmarks' => 'added_bookmark',
            in_array($name, ['reader.lessons.store']) || ($method === 'POST' && in_array($uri, ['api/lessons', 'author/lessons'])) => 'shared_lesson',
            in_array($name, ['reader.recommendations.store']) || ($method === 'POST' && in_array($uri, ['api/recommendations', 'reader/recommendations'])) => 'recommended_book',
            in_array($name, ['reader.books.reviews.store']) || ($method === 'POST' && $uri === 'api/reviews') => 'reviewed_book',
            in_array($name, ['reader.duels.store']) || ($method === 'POST' && $uri === 'api/duels') => 'challenged_reader',
            in_array($name, ['reader.duels.respond']) || preg_match('#^api/duels/[^/]+/respond$#', $uri) === 1 => 'responded_duel',
            $method === 'POST' && preg_match('#^api/duels/[^/]+/answers$#', $uri) === 1 => 'submitted_duel_answers',
            $method === 'POST' && preg_match('#^api/readers/[^/]+/like$#', $uri) === 1 => 'sent_reader_like',
            $method === 'PATCH' && preg_match('#^api/like-requests/[^/]+/respond$#', $uri) === 1 => 'responded_like_request',
            in_array($name, ['reader.shows.rsvp.store']) || preg_match('#^api/shows/[^/]+/rsvp$#', $uri) === 1 => 'joined_show',
            in_array($name, ['reader.shows.applications.store']) || preg_match('#^api/shows/[^/]+/applications$#', $uri) === 1 => 'applied_show',
            default => null,
        };
    }

    private function isViewEvent(string $activityKey): bool
    {
        return Str::startsWith($activityKey, ['opened_', 'browsed_']);
    }
}
