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
        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('exam_id')
                ->constrained('exams')
                ->cascadeOnDelete();
            
            $table->foreignId('murid_id')
                ->constrained('murids')
                ->cascadeOnDelete();
            
            $table->dateTime('started_at');
            $table->dateTime('submitted_at')->nullable();
            $table->integer('duration_minutes')->nullable(); // durasi waktu mengerjakan
            $table->integer('score')->nullable(); // total skor
            $table->decimal('grade', 5, 2)->nullable(); // nilai akhir
            
            $table->enum('status', ['In Progress', 'Submitted', 'Graded'])->default('In Progress');
            
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Satu murid hanya bisa attempt exam sekali (atau bisa dihapus constraint ini jika boleh retry)
            $table->unique(['exam_id', 'murid_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_attempts');
    }
};
