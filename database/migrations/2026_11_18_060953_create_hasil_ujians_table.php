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
        Schema::create('hasil_ujians', function (Blueprint $table) {
        $table->id();
        $table->foreignId('ujian_id')->nullable()->constrained('ujians')->cascadeOnDelete();
        $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
        $table->dateTime('mulai_at')->nullable();
        $table->dateTime('selesai_at')->nullable();
        $table->unsignedInteger('benar')->nullable()->default(0);
        $table->decimal('nilai', 5, 2)->nullable();
        $table->unsignedInteger('pelanggaran')->nullable()->default(0);
        $table->timestamps();
        $table->unique(['ujian_id', 'user_id']);
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hasil_ujians');
    }
};
