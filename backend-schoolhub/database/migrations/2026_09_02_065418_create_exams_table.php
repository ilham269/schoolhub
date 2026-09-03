<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();

            $table->foreignId('kelas_id')
                ->constrained('kelas')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('guru_id')
                ->constrained('gurus')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('mapel_id')
                ->constrained('mapels')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('title');

            $table->text('description')->nullable();

            $table->enum('type', [
                'quiz',
                'uts',
                'uas',
                'ujian',
                'lainnya',
            ])->default('ujian');

            $table->timestamp('start_at');

            $table->timestamp('end_at');

            $table->unsignedInteger('duration_minutes');

            $table->decimal('passing_score', 5, 2)->nullable();

            $table->boolean('is_published')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};