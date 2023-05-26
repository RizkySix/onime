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
        Schema::create('anime_names', function (Blueprint $table) {
            $table->id();
            $table->string('anime_name')->unique();
            $table->string('slug')->unique();
            $table->integer('total_episode');
            $table->float('rating')->default(0);
            $table->string('studio');
            $table->string('author');
            $table->text('description');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anime_names');
    }
};
