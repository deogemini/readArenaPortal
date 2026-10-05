<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table): void {
            $table->string('review_status')->default('graded')->after('passed');
        });

        Schema::create('quiz_attempt_responses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('quiz_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quiz_question_id')->constrained()->cascadeOnDelete();
            $table->text('answer_text')->nullable();
            $table->json('selected_answer_ids')->nullable();
            $table->unsignedTinyInteger('points_awarded')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->unique(['quiz_attempt_id', 'quiz_question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_attempt_responses');

        Schema::table('quiz_attempts', function (Blueprint $table): void {
            $table->dropColumn('review_status');
        });
    }
};
