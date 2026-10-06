<?php

namespace App\Services;

use App\Models\Book;
use App\Models\ReadingGoal;
use App\Models\ReaderShelf;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReaderShelfService
{
    public function setStatus(User $user, Book $book, string $status): ReaderShelf
    {
        abort_unless($user->isReader(), 403);
        if ($book->status !== 'published') {
            throw ValidationException::withMessages(['book' => 'Only published books can be added to your shelf.']);
        }
        if (! in_array($status, ReaderShelf::STATUSES, true)) {
            throw ValidationException::withMessages(['status' => 'Choose a valid reading status.']);
        }

        return DB::transaction(function () use ($user, $book, $status): ReaderShelf {
            $shelf = ReaderShelf::query()
                ->where('user_id', $user->id)
                ->where('book_id', $book->id)
                ->lockForUpdate()
                ->first();
            $wasCompleted = $shelf?->status === 'completed';

            if (! $shelf) {
                $shelf = new ReaderShelf([
                    'user_id' => $user->id,
                    'book_id' => $book->id,
                ]);
            }

            $shelf->status = $status;
            $shelf->completed_at = $status === 'completed'
                ? ($shelf->completed_at ?? ($wasCompleted ? $shelf->updated_at : now()))
                : null;
            $shelf->save();

            $this->refreshBookGoals($user);

            return $shelf->fresh();
        });
    }

    public function remove(User $user, Book $book): void
    {
        abort_unless($user->isReader(), 403);
        DB::transaction(function () use ($user, $book): void {
            ReaderShelf::query()->where('user_id', $user->id)->where('book_id', $book->id)->delete();
            $this->refreshBookGoals($user);
        });
    }

    public function refreshBookGoals(User $user): void
    {
        $goals = ReadingGoal::query()
            ->where('user_id', $user->id)
            ->where('goal_type', 'books')
            ->get();

        foreach ($goals as $goal) {
            $completedBooks = ReaderShelf::query()
                ->where('user_id', $user->id)
                ->where('status', 'completed')
                ->whereNotNull('completed_at')
                ->whereDate('completed_at', '>=', $goal->start_date)
                ->whereDate('completed_at', '<=', $goal->end_date)
                ->count();
            $currentValue = min((int) $goal->target_value, $completedBooks);

            $status = $currentValue >= (int) $goal->target_value ? 'achieved' : 'active';
            if ((int) $goal->current_value !== $currentValue || $goal->status !== $status) {
                $goal->update(['current_value' => $currentValue, 'status' => $status]);
            }
        }
    }
}
