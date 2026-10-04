<?php

namespace App\Services;

use App\Models\Bookmark;
use App\Models\Book;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class BookmarkService
{
    public function create(User $user, Book $book, int $pageNumber, ?string $label): Bookmark
    {
        abort_unless($user->isReader(), 403);
        abort_unless($book->status === 'published', 404);

        return Bookmark::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'page_number' => $pageNumber,
            'label' => $label,
        ]);
    }

    public function update(User $user, Bookmark $bookmark, int $pageNumber, ?string $label): Bookmark
    {
        $this->assertOwner($user, $bookmark);
        $bookmark->update(['page_number' => $pageNumber, 'label' => $label]);

        return $bookmark->fresh('book');
    }

    public function delete(User $user, Bookmark $bookmark): void
    {
        $this->assertOwner($user, $bookmark);
        $bookmark->delete();
    }

    private function assertOwner(User $user, Bookmark $bookmark): void
    {
        if ((int) $bookmark->user_id !== (int) $user->id) {
            throw new AuthorizationException;
        }
    }
}
