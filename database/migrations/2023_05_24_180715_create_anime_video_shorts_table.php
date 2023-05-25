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
        Schema::create('anime_video_shorts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anime_video_id');
            $table->string('short_name');
            $table->float('duration')->default(10);
            $table->string('short_url');
            $table->timestamps(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anime_video_shorts');
    }
};
