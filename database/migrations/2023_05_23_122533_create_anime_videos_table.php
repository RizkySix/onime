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
        Schema::create('anime_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anime_name_id');
            $table->string('anime_eps');
            $table->integer('resolution');
            $table->float('duration');
            $table->string('video_format');
            $table->string('video_url');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anime_videos');
    }
};
