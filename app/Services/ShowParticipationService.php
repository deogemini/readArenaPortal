<?php

namespace App\Services;

use App\Models\LiveShow;
use App\Models\QuizAttempt;
use App\Models\ShowApplication;
use App\Models\ShowRsvp;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ShowParticipationService
{
    public function rsvp(User $user, LiveShow $show): ShowRsvp
    {
        return DB::transaction(function () use ($user, $show) {
            $lockedShow = LiveShow::query()->whereKey($show->id)->lockForUpdate()->firstOrFail();
            $this->assertUpcoming($lockedShow);

            return ShowRsvp::firstOrCreate([
                'live_show_id' => $lockedShow->id,
                'user_id' => $user->id,
            ]);
        });
    }

    public function cancelRsvp(User $user, LiveShow $show): void
    {
        $show->rsvps()->where('user_id', $user->id)->delete();
    }

    public function apply(User $user, LiveShow $show, string $motivation): ShowApplication
    {
        $this->assertUpcoming($show);
        if (! $show->book_id) {
            throw ValidationException::withMessages(['show' => 'This show is not linked to a book yet.']);
        }

        $bestPassedScore = QuizAttempt::query()
            ->join('quizzes', 'quizzes.id', '=', 'quiz_attempts.quiz_id')
            ->join('books', 'books.id', '=', 'quizzes.book_id')
            ->where('quiz_attempts.user_id', $user->id)
            ->where('quiz_attempts.passed', true)
            ->where('quizzes.book_id', $show->book_id)
            ->where('quizzes.status', 'published')
            ->where('books.status', 'published')
            ->max('quiz_attempts.score');

        if ($bestPassedScore === null) {
            throw ValidationException::withMessages(['show' => 'Pass a published quiz for the related book before applying as a guest.']);
        }

        return DB::transaction(function () use ($user, $show, $motivation, $bestPassedScore) {
            $lockedShow = LiveShow::query()->whereKey($show->id)->lockForUpdate()->firstOrFail();
            $this->assertUpcoming($lockedShow);
            $application = ShowApplication::query()->firstOrNew([
                'live_show_id' => $lockedShow->id,
                'user_id' => $user->id,
            ]);
            if ($application->exists && in_array($application->status, ['pending', 'approved'], true)) {
                throw ValidationException::withMessages(['show' => 'You already have an active guest application for this show.']);
            }

            $application->fill([
                'motivation' => $motivation,
                'quiz_score' => (int) $bestPassedScore,
                'status' => 'pending',
            ])->save();

            return $application->fresh(['show', 'user']);
        });
    }

    public function withdraw(User $user, ShowApplication $application): ShowApplication
    {
        abort_unless((int) $application->user_id === (int) $user->id, 404);
        if ($application->status !== 'pending') {
            throw ValidationException::withMessages(['application' => 'Only pending applications can be withdrawn.']);
        }

        $application->update(['status' => 'withdrawn']);

        return $application->fresh(['show', 'user']);
    }

    public function review(ShowApplication $application, string $status): ShowApplication
    {
        if (! in_array($status, ['approved', 'rejected'], true)) {
            throw ValidationException::withMessages(['status' => 'Choose approved or rejected.']);
        }
        if ($application->status !== 'pending') {
            throw ValidationException::withMessages(['application' => 'Only pending applications can be reviewed.']);
        }

        return DB::transaction(function () use ($application, $status) {
            $locked = ShowApplication::query()->whereKey($application->id)->lockForUpdate()->firstOrFail();
            if ($locked->status !== 'pending') {
                throw ValidationException::withMessages(['application' => 'Only pending applications can be reviewed.']);
            }
            $locked->update(['status' => $status]);

            return $locked->fresh(['show', 'user']);
        });
    }

    private function assertUpcoming(LiveShow $show): void
    {
        if ($show->status !== 'scheduled' || ! $show->start_at || $show->start_at->isPast()) {
            throw ValidationException::withMessages(['show' => 'This show is not open for registration.']);
        }
    }
}
