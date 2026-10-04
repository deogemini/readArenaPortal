<?php

namespace App\Services;

use App\Models\Book;
use App\Models\ReaderShelf;
use App\Models\User;
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

        return ReaderShelf::query()->updateOrCreate(
            ['user_id' => $user->id, 'book_id' => $book->id],
            ['status' => $status],
        );
    }

    public function remove(User $user, Book $book): void
    {
        abort_unless($user->isReader(), 403);
        ReaderShelf::query()->where('user_id', $user->id)->where('book_id', $book->id)->delete();
    }
}
