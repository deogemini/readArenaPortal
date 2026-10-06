<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserAccountDeletionService
{
    public function delete(User $user): void
    {
        $profilePhoto = $user->profile_photo_path;
        $ideaAttachments = $user->readerIdeas()
            ->whereNotNull('attachment_path')
            ->pluck('attachment_path')
            ->filter()
            ->values()
            ->all();

        $user->tokens()->delete();
        $user->notifications()->delete();

        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))
                ->where('user_id', $user->id)
                ->delete();
        }

        DB::table('password_reset_tokens')->where('email', $user->email)->delete();

        $user->delete();

        if ($profilePhoto) {
            Storage::disk('public')->delete($profilePhoto);
        }

        if ($ideaAttachments !== []) {
            Storage::disk('local')->delete($ideaAttachments);
        }
    }
}
