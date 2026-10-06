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
        Schema::create('watch_items', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->enum('type',['Movie', 'Drama', 'Series', 'Tv Show']);
            $table->integer('release_year');
            $table->text('genre');
            $table->string('country', 100)->nullable();
            $table->string('director_creator', 150)->nullable();
            $table->text('cast_list')->nullable();
            $table->text('synopsis')->nullable();
            $table->string('poster')->nullable();
            $table->integer('duration')->nullable();
            $table->integer('total_episodes')->nullable();
            $table->decimal('rating', 3, 1)->nullable();
            $table->text('review')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('watch-items');
    }
};
