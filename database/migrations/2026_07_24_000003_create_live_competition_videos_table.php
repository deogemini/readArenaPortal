<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_competition_videos', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('live_show_id')->nullable()->constrained('live_shows')->nullOnDelete();
            $table->string('video_path');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('published');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_competition_videos');
    }
};
