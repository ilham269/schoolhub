<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mapels', function (Blueprint $table) {
            if (! Schema::hasColumn('mapels', 'kkm')) {
                $table->unsignedTinyInteger('kkm')->default(75)->after('jumlah_jam');
            }
        });

        Schema::table('jadwals', function (Blueprint $table) {
            if (! Schema::hasColumn('jadwals', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('ruang');
            }
        });

        Schema::table('gurus', function (Blueprint $table) {
            if (! Schema::hasColumn('gurus', 'nama_lengkap_guru')) {
                $table->string('nama_lengkap_guru')->nullable()->after('nip');
            }
        });

        Schema::table('murids', function (Blueprint $table) {
            if (! Schema::hasColumn('murids', 'Nama_lengkap_murid')) {
                $table->string('Nama_lengkap_murid')->nullable()->after('nis');
            }
        });
    }

    public function down(): void
    {
        Schema::table('mapels', function (Blueprint $table) {
            if (Schema::hasColumn('mapels', 'kkm')) {
                $table->dropColumn('kkm');
            }
        });

        Schema::table('jadwals', function (Blueprint $table) {
            if (Schema::hasColumn('jadwals', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });

        Schema::table('gurus', function (Blueprint $table) {
            if (Schema::hasColumn('gurus', 'nama_lengkap_guru')) {
                $table->dropColumn('nama_lengkap_guru');
            }
        });

        Schema::table('murids', function (Blueprint $table) {
            if (Schema::hasColumn('murids', 'Nama_lengkap_murid')) {
                $table->dropColumn('Nama_lengkap_murid');
            }
        });
    }
};
