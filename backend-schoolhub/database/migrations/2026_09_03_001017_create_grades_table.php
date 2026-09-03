<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();

            $table->foreignId('murid_id')
                ->constrained('murids')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('mapel_id')
                ->constrained('mapels')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('exam_attempt_id')
                ->nullable()
                ->constrained('exam_attempts')
                ->nullOnDelete();

            $table->decimal('score', 5, 2);

            $table->string('type')->default('exam');

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};