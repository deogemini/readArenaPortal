<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('duels', function (Blueprint $table): void {
            $table->foreignId('quiz_id')->nullable()->after('book_id')->constrained()->nullOnDelete();
            $table->timestamp('completed_at')->nullable()->after('status');
        });

        Schema::create('duel_attempts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('duel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('score');
            $table->json('responses');
            $table->timestamp('submitted_at');
            $table->timestamps();
            $table->unique(['duel_id', 'user_id']);
        });

        Schema::create('reader_like_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('recipient_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 24)->default('pending')->index();
            $table->timestamps();
            $table->unique(['sender_id', 'recipient_id']);
            $table->index(['recipient_id', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reader_like_requests');
        Schema::dropIfExists('duel_attempts');

        Schema::table('duels', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('quiz_id');
            $table->dropColumn('completed_at');
        });
    }
};
