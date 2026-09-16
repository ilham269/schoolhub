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
        Schema::create('slip_gajis', function (Blueprint $table) {
            $table->id();
            
            // Foreign Key
            $table->foreignId('karyawan_id')
                ->constrained('karyawans')
                ->cascadeOnDelete()
                ->comment('Foreign key ke karyawans');
            
            // Periode
            $table->date('periode')->comment('Periode gaji (YYYY-MM-01)');
            
            // Komponen Gaji
            $table->decimal('gaji_pokok', 12, 2)->comment('Gaji pokok bulanan');
            $table->decimal('tunjangan', 12, 2)->default(0)->comment('Total tunjangan (jabatan, transport, dll)');
            $table->decimal('bonus', 12, 2)->default(0)->comment('Bonus/insentif');
            $table->decimal('potongan', 12, 2)->default(0)->comment('Potongan (kasbon, BPJS, dll)');
            $table->decimal('total_gaji', 12, 2)->comment('gaji_pokok + tunjangan + bonus - potongan');
            
            // Status & Slip Info
            $table->enum('status', ['DRAFT', 'APPROVED', 'PAID'])
                ->default('DRAFT')
                ->comment('Status slip gaji');
            $table->string('slip_number', 50)->unique()->comment('Format: SLIP/GAJ/YYYYMM/00001');
            $table->string('file_path')->nullable()->comment('Path file PDF slip gaji');
            $table->text('catatan')->nullable()->comment('Catatan khusus (potongan kasbon, dll)');
            
            // Audit Trail
            $table->foreignId('dibuat_oleh')
                ->constrained('users')
                ->cascadeOnDelete()
                ->comment('User (Admin/Karyawan TU) yang generate');
            $table->foreignId('approved_oleh')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('User yang approve');
            $table->timestamp('approved_at')->nullable()->comment('Waktu approval');
            $table->timestamp('paid_at')->nullable()->comment('Waktu pembayaran (status → PAID)');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['karyawan_id', 'periode'], 'idx_slip_gaji_karyawan_periode');
            $table->index('status', 'idx_slip_gaji_status');
            
            // Unique Constraint: Satu karyawan hanya boleh 1 slip per periode
            $table->unique(['karyawan_id', 'periode'], 'unique_karyawan_periode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slip_gajis');
    }
};
