<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reader_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->string('client_id', 80);
            $table->text('content');
            $table->unsignedInteger('page_number');
            $table->timestamps();

            $table->unique(['user_id', 'book_id', 'client_id']);
            $table->index(['user_id', 'book_id', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reader_notes');
    }
};
