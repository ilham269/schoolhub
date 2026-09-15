<?php

declare(strict_types=1);

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
        Schema::create('payment_gateway_logs', function (Blueprint $table) {
            $table->id();
            
            // Foreign Key (nullable karena bisa log error sebelum pembayaran terbuat)
            $table->foreignId('pembayaran_id')
                ->nullable()
                ->constrained('pembayarans')
                ->nullOnDelete()
                ->comment('Foreign key ke pembayarans');
            
            // Gateway & Action
            $table->string('gateway', 50)->comment('Provider: midtrans, xendit');
            $table->string('action', 50)->comment('create_token, callback, refund, cancel, etc');
            
            // Request & Response
            $table->json('request_payload')->nullable()->comment('Data yang dikirim ke gateway');
            $table->json('response_payload')->nullable()->comment('Response dari gateway');
            $table->smallInteger('http_status')->nullable()->comment('HTTP status code (200, 400, 500, dll)');
            $table->text('error_message')->nullable()->comment('Error message jika gagal');
            
            // Metadata
            $table->string('ip_address', 45)->nullable()->comment('IP address gateway/server');
            $table->text('user_agent')->nullable()->comment('User agent dari request');
            
            $table->timestamp('created_at')->useCurrent()->comment('Waktu event terjadi');
            
            // Indexes
            $table->index('pembayaran_id', 'idx_gateway_log_pembayaran_id');
            $table->index('gateway', 'idx_gateway_log_gateway');
            $table->index('action', 'idx_gateway_log_action');
            $table->index('created_at', 'idx_gateway_log_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_gateway_logs');
    }
};
