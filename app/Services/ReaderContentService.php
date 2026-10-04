<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Lesson;
use App\Models\QuizAttempt;
use App\Models\Recommendation;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

class ReaderContentService
{
    public function storeLesson(User $user, array $data): Lesson
    {
        $this->assertCanPublish($user, (int) $data['book_id'], $data['status']);

        return Lesson::create($data + ['user_id' => $user->id]);
    }

    public function updateLesson(User $user, Lesson $lesson, array $data): Lesson
    {
        $this->assertOwner($user, $lesson->user_id);
        $this->assertCanPublish($user, (int) $data['book_id'], $data['status']);
        $lesson->update($data);

        return $lesson->fresh('book');
    }

    public function deleteLesson(User $user, Lesson $lesson): void
    {
        $this->assertOwner($user, $lesson->user_id);
        $lesson->delete();
    }

    public function storeRecommendation(User $user, array $data): Recommendation
    {
        $this->assertCanPublish($user, (int) $data['book_id'], $data['status']);

        return Recommendation::create($data + ['user_id' => $user->id]);
    }

    public function updateRecommendation(User $user, Recommendation $recommendation, array $data): Recommendation
    {
        $this->assertOwner($user, $recommendation->user_id);
        $this->assertCanPublish($user, (int) $data['book_id'], $data['status']);
        $recommendation->update($data);

        return $recommendation->fresh('book');
    }

    public function deleteRecommendation(User $user, Recommendation $recommendation): void
    {
        $this->assertOwner($user, $recommendation->user_id);
        $recommendation->delete();
    }

    private function assertOwner(User $user, int $ownerId): void
    {
        if ($user->id !== $ownerId) {
            throw new AuthorizationException;
        }
    }

    private function assertCanPublish(User $user, int $bookId, string $status): void
    {
        $book = Book::query()->whereKey($bookId)->where('status', 'published')->exists();
        if (! $book) {
            throw ValidationException::withMessages(['book_id' => 'Choose a published book.']);
        }

        if ($status !== 'published') {
            return;
        }

        $verified = QuizAttempt::query()
            ->where('user_id', $user->id)
            ->where('passed', true)
            ->whereHas('quiz', fn ($quiz) => $quiz->where('book_id', $bookId)->where('status', 'published'))
            ->exists();

        if (! $verified) {
            throw ValidationException::withMessages([
                'status' => 'Pass a quiz for this book before publishing a lesson or recommendation.',
            ]);
        }
    }
}
