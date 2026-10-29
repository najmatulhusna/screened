<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel transactions aslinya dibuat manual di database (tidak ada migration-nya).
 * Migration ini hanya MENCIPATAKAN kalau tabel belum ada, jadi aman dijalankan
 * di database yang sudah punya tabel tersebut (no-op).
 * Kolom duration_days (berlaku berapa hari) digabung di sini (dulu file terpisah).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('transactions')) {
            // DB lama: tabel sudah ada tapi kolom durasi belum -> tambahkan
            if (! Schema::hasColumn('transactions', 'duration_days')) {
                Schema::table('transactions', function (Blueprint $table) {
                    $table->unsignedInteger('duration_days')->nullable()->after('amount');
                });
            }

            return;
        }

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('users_id')->index();
            $table->string('type', 30)->default('vip');
            $table->decimal('amount', 12, 2);
            $table->unsignedInteger('duration_days')->nullable(); // berlaku berapa hari
            $table->string('status', 20)->default('pending')->index();
            $table->string('payment_method', 50)->nullable();
            $table->string('midtrans_order_id', 100)->nullable()->unique();
            $table->text('snap_token')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // jangan dihapus: tabel ini bukan milik migration ini
    }
};
