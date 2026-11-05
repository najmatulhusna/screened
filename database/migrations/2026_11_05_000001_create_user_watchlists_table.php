<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * My Album: album tontonan pribadi tiap user (fitur VIP).
 *
 * - watch_item_id terisi  -> ditambahkan dari Collection (snapshot data katalog).
 * - watch_item_id NULL    -> ditambahkan manual (tidak ada di Collection publik).
 * - Data tidak pernah disalin balik ke watch_items (katalog tetap milik admin).
 * - Unique (user_id, watch_item_id) mencegah judul Collection masuk dua kali
 *   (NULL boleh banyak kali di MySQL, jadi entri manual tidak terhalang).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_watchlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->foreignId('watch_item_id')
                  ->nullable()
                  ->constrained('watch_items')
                  ->nullOnDelete();

            // Snapshot data Collection / input manual
            $table->string('title', 255);
            $table->enum('type', ['Movie', 'Drama', 'Series', 'Tv Show'])->default('Movie');
            $table->integer('release_year')->nullable();
            $table->text('genre')->nullable();
            $table->string('country', 100)->nullable();
            $table->string('director_creator', 150)->nullable();
            $table->text('cast_list')->nullable();
            $table->text('synopsis')->nullable();
            $table->text('poster')->nullable();
            $table->integer('duration')->nullable();
            $table->integer('total_episodes')->nullable();

            // Data personal pengguna
            $table->enum('watch_status', ['Plan to Watch', 'Watching', 'Completed', 'On Hold', 'Dropped'])
                  ->default('Plan to Watch');
            $table->decimal('personal_rating', 3, 1)->nullable();
            $table->date('date_started')->nullable();
            $table->date('date_completed')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'watch_item_id']);
            $table->index('watch_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_watchlists');
    }
};
