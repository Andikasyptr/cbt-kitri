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
        Schema::create('kelas', function (Blueprint $table) {
        $table->id();
        $table->unsignedTinyInteger('tingkat')->nullable();
        $table->foreignId('jurusan_id')->nullable()->constrained('jurusans')->cascadeOnDelete();
        $table->string('kode', 10)->nullable();
        $table->timestamps();
        $table->unique(['tingkat', 'jurusan_id', 'kode']);
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelas');
    }
};
