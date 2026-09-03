<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tugas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('kelas_id')
                ->constrained('kelas')
                ->cascadeOnDelete();

            $table->foreignId('guru_id')
                ->constrained('gurus')
                ->cascadeOnDelete();

            $table->foreignId('mapel_id')
                ->constrained('mapels')
                ->cascadeOnDelete();

            $table->foreignId('materi_id')
                ->nullable()
                ->constrained('materis')
                ->nullOnDelete();

            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->text('instruksi')->nullable();
            $table->string('file_path')->nullable();
            $table->dateTime('tanggal_dibuat');
            $table->dateTime('deadline');
            $table->integer('nilai_maksimal')->default(100);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tugas');
    }
};