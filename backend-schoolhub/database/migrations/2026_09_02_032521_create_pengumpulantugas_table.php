<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengumpulan_tugas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tugas_id')
                ->constrained('tugas')
                ->cascadeOnDelete();

            $table->foreignId('murid_id')
                ->constrained('murids')
                ->cascadeOnDelete();

            $table->string('file_path')->nullable();
            $table->string('link')->nullable();
            $table->text('catatan')->nullable();
            $table->dateTime('tanggal_pengumpulan');
            $table->integer('nilai')->nullable();
            $table->text('feedback')->nullable();
            $table->enum('status', ['Belum Dinilai', 'Sudah Dinilai', 'Terlambat'])->default('Belum Dinilai');

            $table->timestamps();

            $table->unique(['tugas_id', 'murid_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengumpulan_tugas');
    }
};