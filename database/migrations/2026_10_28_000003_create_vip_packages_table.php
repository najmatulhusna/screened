<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Paket VIP (harga + berlaku berapa hari) — dikelola admin di halaman Transaksi. */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('vip_packages')) {
            return;
        }

        Schema::create('vip_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60);
            $table->unsignedBigInteger('price');           // rupiah
            $table->unsignedInteger('duration_days');      // berlaku berapa hari
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vip_packages');
    }
};
