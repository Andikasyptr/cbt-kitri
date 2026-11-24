<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_soals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('mapel_id')->nullable()->constrained('mapels')->nullOnDelete();
            $table->string('sumber')->nullable();          // judul ujian asal
            $table->text('pertanyaan')->nullable();
            $table->text('opsi_a')->nullable();
            $table->text('opsi_b')->nullable();
            $table->text('opsi_c')->nullable();
            $table->text('opsi_d')->nullable();
            $table->text('opsi_e')->nullable();
            $table->char('kunci', 1)->nullable();
            $table->string('hash', 40)->nullable();        // pencegah soal ganda
            $table->boolean('ada_gambar')->nullable()->default(false);
            $table->string('gambar_mime')->nullable();
            $table->longText('gambar_data')->nullable();   // base64
            $table->unsignedInteger('dipakai')->nullable()->default(0);
            $table->timestamps();

            $table->unique(['guru_id', 'hash']);
        });

        // Penanda: ujian ini sudah disalin ke bank
        Schema::table('ujians', function (Blueprint $table) {
            $table->dateTime('diarsipkan_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('ujians', function (Blueprint $table) {
            $table->dropColumn('diarsipkan_at');
        });
        Schema::dropIfExists('bank_soals');
    }
};