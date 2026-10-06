<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('show_applications', function (Blueprint $table): void {
            $table->foreignId('book_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
        });

        DB::table('show_applications')->orderBy('id')->chunkById(200, function ($applications): void {
            foreach ($applications as $application) {
                $bookId = DB::table('live_shows')->where('id', $application->live_show_id)->value('book_id');
                if ($bookId) {
                    DB::table('show_applications')->where('id', $application->id)->update(['book_id' => $bookId]);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('show_applications', function (Blueprint $table): void {
            $table->dropForeign(['book_id']);
            $table->dropColumn('book_id');
        });
    }
};
