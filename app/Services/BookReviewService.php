<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BookReview;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class BookReviewService
{
    public function create(User $user, Book $book, array $data): BookReview
    {
        abort_unless($user->isReader(), 403);
        abort_unless($book->status === 'published', 404);

        return BookReview::create($data + [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => 'pending',
        ]);
    }

    public function update(User $user, BookReview $review, array $data): BookReview
    {
        $this->assertOwner($user, $review);
        $review->update($data + ['status' => 'pending']);

        return $review->fresh(['book', 'user']);
    }

    public function delete(User $user, BookReview $review): void
    {
        $this->assertOwner($user, $review);
        $review->delete();
    }

    private function assertOwner(User $user, BookReview $review): void
    {
        if ((int) $review->user_id !== (int) $user->id) {
            throw new AuthorizationException;
        }
    }
}
