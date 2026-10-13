<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Duel;
use App\Models\QuizAttempt;
use App\Models\ReaderShelf;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DuelService
{
    private const ACTIVE_STATUSES = ['pending', 'accepted', 'scheduled', 'waiting', 'live', 'paused'];

    public function duelBooks()
    {
        return Book::query()
            ->where('status', 'published')
            ->whereHas('quizzes', fn ($quizzes) => $quizzes
                ->where('status', 'published')
                ->whereHas('questions')
                ->whereDoesntHave('questions', fn ($questions) => $questions->where('question_type', 'written_response')))
            ->orderBy('title')
            ->get(['id', 'title', 'slug', 'cover_image', 'status']);
    }

    public function onlineOpponents(User $user)
    {
        return User::query()
            ->where('role', 'reader')
            ->whereKeyNot($user->id)
            ->where('last_seen_at', '>=', now()->subMinutes(2))
            ->orderBy('name')
            ->get(['id', 'name', 'username', 'profile_photo_path', 'last_seen_at']);
    }

    public function duelAccess(User $user): array
    {
        $hasCompletedBook = ReaderShelf::query()
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->exists();
        $hasPassedQuiz = $this->hasPassedQuiz($user);
        $used = $this->usedDuelCount($user);
        $limit = max(0, (int) config('gamification.starter_duel_limit', 3));
        $unlocked = $hasCompletedBook || $hasPassedQuiz;

        return [
            'unlimited_duels_unlocked' => $unlocked,
            'can_start_duel' => $unlocked || $used < $limit,
            'starter_duel_limit' => $limit,
            'starter_duels_used' => $used,
            'starter_duels_remaining' => $unlocked ? null : max(0, $limit - $used),
            'unlock_reason' => $hasCompletedBook ? 'completed_book' : ($hasPassedQuiz ? 'passed_quiz' : null),
        ];
    }

    public function verifiedBooks(User $user)
    {
        return Book::query()
            ->where('status', 'published')
            ->whereHas('quizzes', fn ($quizzes) => $quizzes
                ->where('status', 'published')
                ->whereHas('attempts', fn ($attempts) => $attempts
                    ->where('user_id', $user->id)
                    ->where('passed', true)))
            ->orderBy('title')
            ->get(['id', 'title', 'slug', 'cover_image', 'status']);
    }

    public function verifiedOpponents(User $user, Book $book)
    {
        $this->assertPublishedBook($book);

        $verifiedUserIds = QuizAttempt::query()
            ->where('passed', true)
            ->whereHas('quiz', fn ($quizzes) => $quizzes
                ->where('book_id', $book->id)
                ->where('status', 'published'))
            ->select('user_id')
            ->distinct();

        return User::query()
            ->where('role', 'reader')
            ->whereKeyNot($user->id)
            ->whereIn('id', $verifiedUserIds)
            ->orderBy('name')
            ->get(['id', 'name', 'username', 'profile_photo_path', 'last_seen_at']);
    }

    public function challenge(User $challenger, int $opponentId, int $bookId): Duel
    {
        $this->assertReader($challenger);
        if ($challenger->id === $opponentId) {
            throw ValidationException::withMessages(['opponent_id' => 'You cannot challenge yourself.']);
        }

        return DB::transaction(function () use ($challenger, $opponentId, $bookId) {
            $lockedUsers = User::query()
                ->whereIn('id', collect([$challenger->id, $opponentId])->sort()->values())
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $challenger = $lockedUsers->firstWhere('id', $challenger->id);
            $opponent = $lockedUsers->firstWhere('id', $opponentId);
            if (! $opponent || ! $opponent->isReader()) {
                throw ValidationException::withMessages(['opponent_id' => 'Choose an active reader.']);
            }

            $this->assertCanStartDuel($challenger);

            $book = Book::query()
                ->whereKey($bookId)
                ->where('status', 'published')
                ->whereHas('quizzes', fn ($quizzes) => $quizzes
                    ->where('status', 'published')
                    ->whereHas('questions')
                    ->whereDoesntHave('questions', fn ($questions) => $questions->where('question_type', 'written_response')))
                ->first();
            if (! $book) {
                throw ValidationException::withMessages(['book_id' => 'Choose a published book with a playable quiz.']);
            }

            $existing = Duel::query()
                ->where('book_id', $book->id)
                ->whereIn('status', self::ACTIVE_STATUSES)
                ->where(function ($query) use ($challenger, $opponent) {
                    $query->where(function ($pair) use ($challenger, $opponent) {
                        $pair->where('challenger_id', $challenger->id)->where('opponent_id', $opponent->id);
                    })->orWhere(function ($pair) use ($challenger, $opponent) {
                        $pair->where('challenger_id', $opponent->id)->where('opponent_id', $challenger->id);
                    });
                })
                ->exists();

            if ($existing) {
                throw ValidationException::withMessages(['duel' => 'An active duel already exists between these readers for this book.']);
            }

            return Duel::create([
                'book_id' => $book->id,
                'challenger_id' => $challenger->id,
                'opponent_id' => $opponent->id,
                'status' => 'pending',
            ]);
        });
    }

    public function respond(User $user, Duel $duel, string $action): Duel
    {
        $this->assertReader($user);
        if (! in_array($action, ['accept', 'reject'], true)) {
            throw ValidationException::withMessages(['action' => 'Choose accept or reject.']);
        }

        return DB::transaction(function () use ($user, $duel, $action) {
            $lockedUsers = User::query()
                ->whereIn('id', [$duel->challenger_id, $duel->opponent_id])
                ->orderBy('id')
                ->lockForUpdate()
                ->get();
            $lockedUser = $lockedUsers->firstWhere('id', $user->id);
            if (! $lockedUser) {
                abort(404);
            }

            $locked = Duel::query()->whereKey($duel->id)->lockForUpdate()->firstOrFail();
            abort_unless((int) $locked->opponent_id === (int) $user->id, 403);
            if ($locked->status !== 'pending') {
                throw ValidationException::withMessages(['duel' => 'Only pending invitations can be answered.']);
            }

            if ($action === 'accept') {
                $this->assertCanStartDuel($lockedUser);
            }

            $locked->update(['status' => $action === 'accept' ? 'accepted' : 'rejected']);

            return $locked->fresh(['book', 'challenger', 'opponent']);
        });
    }

    public function cancel(User $user, Duel $duel): Duel
    {
        $this->assertReader($user);

        return DB::transaction(function () use ($user, $duel) {
            $locked = Duel::query()->whereKey($duel->id)->lockForUpdate()->firstOrFail();
            abort_unless((int) $locked->challenger_id === (int) $user->id, 403);
            if (! in_array($locked->status, ['pending', 'accepted', 'scheduled'], true)) {
                throw ValidationException::withMessages(['duel' => 'This duel can no longer be cancelled.']);
            }

            $locked->update(['status' => 'cancelled']);

            return $locked->fresh(['book', 'challenger', 'opponent']);
        });
    }

    private function hasPassedQuiz(User $user): bool
    {
        return QuizAttempt::query()
            ->where('user_id', $user->id)
            ->where('review_status', 'graded')
            ->where('passed', true)
            ->whereHas('quiz', fn ($quizzes) => $quizzes
                ->where('status', 'published')
                ->whereHas('book', fn ($books) => $books->where('status', 'published')))
            ->exists();
    }

    private function usedDuelCount(User $user): int
    {
        return Duel::query()
            ->where(fn ($participants) => $participants
                ->where('challenger_id', $user->id)
                ->orWhere('opponent_id', $user->id))
            ->where(fn ($status) => $status
                ->whereIn('status', ['accepted', 'scheduled', 'waiting', 'live', 'paused', 'completed'])
                ->orWhere(fn ($pending) => $pending
                    ->where('status', 'pending')
                    ->where('challenger_id', $user->id)))
            ->count();
    }

    private function assertCanStartDuel(User $user): void
    {
        $access = $this->duelAccess($user);
        if (! $access['can_start_duel']) {
            throw ValidationException::withMessages([
                'duel' => 'You have used all three starter duels. Complete one book or pass a quiz to unlock more duels.',
            ]);
        }
    }

    public function isVerified(User $user, Book $book): bool
    {
        return QuizAttempt::query()
            ->where('user_id', $user->id)
            ->where('passed', true)
            ->whereHas('quiz', fn ($quizzes) => $quizzes
                ->where('book_id', $book->id)
                ->where('status', 'published'))
            ->exists();
    }

    private function assertVerified(User $user, Book $book): void
    {
        if (! $this->isVerified($user, $book)) {
            throw ValidationException::withMessages([
                'book_id' => $user->id === auth()->id()
                    ? 'Pass a published quiz for this book before challenging a reader.'
                    : 'The selected reader has not passed a published quiz for this book.',
            ]);
        }
    }

    private function assertPublishedBook(Book $book): void
    {
        if ($book->status !== 'published') {
            throw ValidationException::withMessages(['book_id' => 'Choose a published book.']);
        }
    }

    private function assertReader(User $user): void
    {
        abort_unless($user->isReader(), 403);
    }
}
