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
        Schema::create('soals', function (Blueprint $table) {
        $table->id();
        $table->foreignId('ujian_id')->nullable()->constrained('ujians')->cascadeOnDelete();
        $table->text('pertanyaan')->nullable();
        $table->text('opsi_a')->nullable();
        $table->text('opsi_b')->nullable();
        $table->text('opsi_c')->nullable();
        $table->text('opsi_d')->nullable();
        $table->text('opsi_e')->nullable();
        $table->char('kunci', 1)->nullable();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soals');
    }
};
