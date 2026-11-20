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
        Schema::create('kelas_ujian', function (Blueprint $table) {
        $table->foreignId('ujian_id')->constrained('ujians')->cascadeOnDelete();
        $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
        $table->primary(['ujian_id', 'kelas_id']);
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelas_ujian');
    }
};
