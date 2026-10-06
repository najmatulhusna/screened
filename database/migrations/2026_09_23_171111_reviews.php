<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table  ->foreignId('watch_items_id')
                    ->constrained('watch_items')
                    ->onDelete('cascade');
            $table  ->foreignId('users_id')
                    ->nullable()
                    ->constrained('users')
                    ->onDelete('cascade');
            $table  ->foreignId('admin_id')
                    ->nullable()
                    ->constrained('admin')
                    ->onDelete('cascade');
            $table->integer('episode_watched')->default(0);
            $table->enum('status', ['Plan to Watch', 'Watching', 'Completed'])->default('Plan to Watch');
            $table->decimal('rating', 3, 1)->nullable();
             $table->text('review_text')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
