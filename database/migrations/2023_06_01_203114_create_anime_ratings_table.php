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
        Schema::create('anime_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anime_name_id');
            $table->integer('point')->default(0);
            $table->integer('participan')->default(0);
            $table->float('rating')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anime_ratings');
    }
};
