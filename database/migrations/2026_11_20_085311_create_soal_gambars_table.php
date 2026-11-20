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
    Schema::create('soal_gambars', function (Blueprint $table) {
        $table->id();
        $table->foreignId('soal_id')->nullable()->unique()->constrained('soals')->cascadeOnDelete();
        $table->string('mime')->nullable();
        $table->longText('data')->nullable(); // gambar dalam base64
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('soal_gambars');
}
};
