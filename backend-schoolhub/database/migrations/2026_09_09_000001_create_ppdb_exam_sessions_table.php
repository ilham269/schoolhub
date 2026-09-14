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
        Schema::create('ppdb_exam_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('ppdb_exams')->cascadeOnDelete();
            $table->foreignId('calon_siswa_id')->constrained('calon_siswas')->cascadeOnDelete();
            $table->foreignId('attempt_id')->nullable()->constrained('ppdb_exam_attempts')->cascadeOnDelete();
            
            // Session tracking
            $table->timestamp('started_at');
            $table->timestamp('expires_at');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            
            // Status: active, expired, submitted, suspended
            $table->enum('status', ['active', 'expired', 'submitted', 'suspended'])->default('active');
            
            // Security data
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('device_fingerprint')->nullable();
            
            // Question randomization (stored as JSON array of question IDs)
            $table->json('question_order')->nullable();
            
            // Suspicious activity tracking
            $table->integer('tab_switches')->default(0);
            $table->integer('fullscreen_exits')->default(0);
            $table->integer('copy_attempts')->default(0);
            $table->integer('paste_attempts')->default(0);
            $table->integer('right_click_attempts')->default(0);
            $table->decimal('suspicion_score', 5, 2)->default(0); // 0-100
            
            // Network disconnections
            $table->integer('disconnect_count')->default(0);
            $table->timestamp('last_disconnect_at')->nullable();
            
            $table->timestamps();
            
            // One active session per student per exam
            $table->unique(['exam_id', 'calon_siswa_id']);
            
            // Indexes for queries
            $table->index('status');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppdb_exam_sessions');
    }
};
