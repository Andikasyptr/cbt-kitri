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
        Schema::create('jawabans', function (Blueprint $table) {
        $table->id();
        $table->foreignId('hasil_ujian_id')->nullable()->constrained('hasil_ujians')->cascadeOnDelete();
        $table->foreignId('soal_id')->nullable()->constrained('soals')->cascadeOnDelete();
        $table->char('jawaban', 1)->nullable();
        $table->timestamps();
        $table->unique(['hasil_ujian_id', 'soal_id']);
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jawabans');
    }
};
