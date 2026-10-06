<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reader_shelves', function (Blueprint $table): void {
            $table->timestamp('completed_at')->nullable()->index();
        });

        DB::table('reader_shelves')
            ->where('status', 'completed')
            ->whereNull('completed_at')
            ->update(['completed_at' => DB::raw('COALESCE(updated_at, created_at, CURRENT_TIMESTAMP)')]);

        DB::table('reading_goals')
            ->where('goal_type', 'books')
            ->orderBy('id')
            ->chunkById(200, function ($goals): void {
                foreach ($goals as $goal) {
                    $completedBooks = DB::table('reader_shelves')
                        ->where('user_id', $goal->user_id)
                        ->where('status', 'completed')
                        ->whereNotNull('completed_at')
                        ->whereDate('completed_at', '>=', $goal->start_date)
                        ->whereDate('completed_at', '<=', $goal->end_date)
                        ->count();
                    $currentValue = min((int) $goal->target_value, $completedBooks);

                    DB::table('reading_goals')->where('id', $goal->id)->update([
                        'current_value' => $currentValue,
                        'status' => $currentValue >= (int) $goal->target_value ? 'achieved' : 'active',
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('reader_shelves', function (Blueprint $table): void {
            $table->dropIndex(['completed_at']);
            $table->dropColumn('completed_at');
        });
    }
};
