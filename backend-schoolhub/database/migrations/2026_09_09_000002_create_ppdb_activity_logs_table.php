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
        Schema::create('ppdb_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('ppdb_exam_sessions')->cascadeOnDelete();
            
            // Event types: tab_switch, fullscreen_exit, copy_attempt, paste_attempt, 
            // right_click, answer_changed, network_disconnect, network_reconnect, 
            // page_visible, page_hidden, session_started, session_ended, auto_save
            $table->string('event', 50);
            
            // Description of the event
            $table->text('description')->nullable();
            
            // Additional metadata (JSON)
            $table->json('metadata')->nullable();
            
            $table->timestamp('created_at');
            
            // Indexes for queries
            $table->index(['session_id', 'event']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppdb_activity_logs');
    }
};
