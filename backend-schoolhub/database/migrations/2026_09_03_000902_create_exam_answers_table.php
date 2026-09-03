<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_answers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('exam_attempt_id')
                ->constrained('exam_attempts')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('question_id')
                ->constrained('questions')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('option_id')
                ->nullable()
                ->constrained('options')
                ->nullOnDelete();

            $table->text('answer_text')->nullable();

            $table->boolean('is_correct')->nullable();

            $table->decimal('score', 5, 2)->nullable();

            $table->timestamps();

            $table->unique([
                'exam_attempt_id',
                'question_id'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_answers');
    }
};