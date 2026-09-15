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
        Schema::table('murids', function (Blueprint $table) {
            // Tempat lahir
            $table->string('tempat_lahir')->nullable()->after('tanggal_lahir');
            
            // Data orang tua/wali (extended)
            $table->string('nama_ayah')->nullable()->after('nama_orangtua');
            $table->string('nama_ibu')->nullable()->after('nama_ayah');
            $table->string('pekerjaan_ayah')->nullable()->after('nama_ibu');
            $table->string('pekerjaan_ibu')->nullable()->after('pekerjaan_ayah');
            $table->string('nomor_telepon_ortu')->nullable()->after('pekerjaan_ibu');
            
            // Informasi tambahan
            $table->string('agama', 50)->nullable()->after('nomor_telepon_ortu');
            $table->integer('anak_ke')->nullable()->after('agama');
            $table->integer('jumlah_saudara')->nullable()->after('anak_ke');
            $table->string('hobi')->nullable()->after('jumlah_saudara');
            $table->string('cita_cita')->nullable()->after('hobi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('murids', function (Blueprint $table) {
            $table->dropColumn([
                'tempat_lahir',
                'nama_ayah',
                'nama_ibu',
                'pekerjaan_ayah',
                'pekerjaan_ibu',
                'nomor_telepon_ortu',
                'agama',
                'anak_ke',
                'jumlah_saudara',
                'hobi',
                'cita_cita',
            ]);
        });
    }
};
