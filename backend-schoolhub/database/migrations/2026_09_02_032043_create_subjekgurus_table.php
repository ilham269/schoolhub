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
        Schema::create('teacher_subjects', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('guru_id')
                ->constrained('gurus')
                ->cascadeOnDelete();

            $table->foreignId('mapel_id')
                ->constrained('mapels')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['guru_id', 'mapel_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_subjects');
    }
};
