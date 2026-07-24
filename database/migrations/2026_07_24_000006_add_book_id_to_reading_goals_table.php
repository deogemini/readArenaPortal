<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reading_goals', function (Blueprint $table) {
            $table->foreignId('book_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('reading_goals', function (Blueprint $table) {
            $table->dropConstrainedForeignId('book_id');
        });
    }
};
