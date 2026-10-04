<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('show_rsvps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_show_id')->constrained('live_shows')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['live_show_id', 'user_id']);
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('show_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_show_id')->constrained('live_shows')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('motivation');
            $table->unsignedTinyInteger('quiz_score');
            $table->string('status')->default('pending');
            $table->timestamps();
            $table->unique(['live_show_id', 'user_id']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('show_applications');
        Schema::dropIfExists('show_rsvps');
    }
};
