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
        Schema::create('ujians', function (Blueprint $table) {
        $table->id();
        $table->foreignId('guru_id')->nullable()->constrained('users')->cascadeOnDelete();
        $table->foreignId('mapel_id')->nullable()->constrained('mapels')->cascadeOnDelete();
        $table->string('judul')->nullable();
        $table->string('token', 10)->nullable();
        $table->unsignedInteger('durasi_menit')->nullable()->default(60);
        $table->dateTime('waktu_mulai')->nullable();
        $table->dateTime('waktu_selesai')->nullable();
        $table->boolean('acak_soal')->nullable()->default(true);
        $table->boolean('aktif')->nullable()->default(true);
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ujians');
    }
};
