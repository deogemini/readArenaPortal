<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->timestamp('last_seen_at')->nullable()->index();
            $table->string('last_seen_platform', 24)->nullable();
        });

        Schema::create('user_activity_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('activity_key', 64);
            $table->string('platform', 24);
            $table->timestamps();
            $table->index(['created_at', 'id']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_activity_events');

        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['last_seen_at']);
            $table->dropColumn(['last_seen_at', 'last_seen_platform']);
        });
    }
};
