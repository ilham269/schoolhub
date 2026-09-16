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
        Schema::create('tagihan_spps', function (Blueprint $table) {
            $table->id();
            
            // Foreign Keys
            $table->foreignId('murid_id')
                ->constrained('murids')
                ->cascadeOnDelete();
            
            // Periode & Nominal
            $table->date('periode')->comment('Format: YYYY-MM-01');
            $table->decimal('jumlah', 12, 2)->comment('Nominal tagihan SPP');
            $table->decimal('denda', 12, 2)->default(0)->comment('Denda keterlambatan');
            $table->decimal('total', 12, 2)->comment('jumlah + denda');
            
            // Payment Info
            $table->date('jatuh_tempo')->comment('Deadline pembayaran');
            $table->enum('status', ['UNPAID', 'PENDING', 'LUNAS', 'EXPIRED', 'CANCELLED'])
                ->default('UNPAID')
                ->comment('Status pembayaran');
            $table->string('invoice_number', 50)->unique()->comment('Format: INV/SPP/YYYYMM/00001');
            
            // PDF & Payment Date
            $table->string('kuitansi_path')->nullable()->comment('Path file PDF kuitansi');
            $table->timestamp('paid_at')->nullable()->comment('Waktu pembayaran berhasil');
            
            // Notes & Audit
            $table->text('notes')->nullable()->comment('Catatan tambahan (dispensasi, keringanan, dll)');
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('User yang generate invoice');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['murid_id', 'periode'], 'idx_tagihan_spp_murid_periode');
            $table->index('status', 'idx_tagihan_spp_status');
            $table->index('jatuh_tempo', 'idx_tagihan_spp_jatuh_tempo');
            
            // Unique Constraint: Satu murid hanya boleh 1 invoice per periode
            $table->unique(['murid_id', 'periode'], 'unique_murid_periode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tagihan_spps');
    }
};
