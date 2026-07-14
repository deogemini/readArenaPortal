<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('genres')) {
            Schema::create('genres', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->timestamps();
            });

            return;
        }

        if (!Schema::hasColumn('genres', 'name')) {
            Schema::table('genres', function (Blueprint $table) {
                $table->string('name')->after('id');
            });
        }

        if (!Schema::hasColumn('genres', 'slug')) {
            Schema::table('genres', function (Blueprint $table) {
                $table->string('slug')->unique()->after('name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('genres');
    }
};
