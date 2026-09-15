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
        Schema::create('pembayarans', function (Blueprint $table) {
            $table->id();
            
            // Foreign Key
            $table->foreignId('tagihan_id')
                ->constrained('tagihan_spps')
                ->cascadeOnDelete()
                ->comment('Foreign key ke tagihan_spps');
            
            // Gateway Info
            $table->string('gateway', 50)->comment('Provider: midtrans, xendit, manual');
            $table->string('transaction_id', 100)->nullable()->comment('Order ID dari gateway');
            $table->text('snap_token')->nullable()->comment('Midtrans Snap Token');
            
            // Payment Details
            $table->string('payment_type', 50)->nullable()->comment('bank_transfer, gopay, alfamart, etc');
            $table->string('va_number', 50)->nullable()->comment('Virtual Account Number');
            $table->string('bank', 50)->nullable()->comment('Nama bank (BCA, Mandiri, BNI, dll)');
            $table->decimal('gross_amount', 12, 2)->comment('Total yang dibayar');
            
            // Status & Timing
            $table->enum('status', ['PENDING', 'SUCCESS', 'FAILED', 'EXPIRED', 'CANCELLED'])
                ->default('PENDING')
                ->comment('Status transaksi');
            $table->timestamp('paid_at')->nullable()->comment('Waktu pembayaran sukses dari gateway');
            $table->timestamp('expired_at')->nullable()->comment('Waktu expired transaksi');
            
            // Callback & Processing
            $table->json('raw_callback')->nullable()->comment('Payload lengkap dari gateway webhook');
            $table->boolean('signature_verified')->default(false)->comment('Flag validasi signature key');
            $table->timestamp('callback_received_at')->nullable()->comment('Waktu pertama terima callback');
            $table->timestamp('processed_by_job_at')->nullable()->comment('Waktu job selesai proses');
            
            $table->timestamps();
            
            // Indexes
            $table->index('tagihan_id', 'idx_pembayaran_tagihan_id');
            $table->index('transaction_id', 'idx_pembayaran_transaction_id');
            $table->index('status', 'idx_pembayaran_status');
            $table->index('gateway', 'idx_pembayaran_gateway');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayarans');
    }
};
