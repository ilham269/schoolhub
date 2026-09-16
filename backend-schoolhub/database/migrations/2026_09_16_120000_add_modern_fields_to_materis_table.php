<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('materis', function (Blueprint $table) {
            $table->string('file')->nullable()->after('file_path');
            $table->timestamp('published_at')->nullable()->after('link');
        });

        DB::table('materis')->whereNull('file')->update(['file' => DB::raw('file_path')]);
        DB::table('materis')->where('is_published', true)->whereNull('published_at')->update(['published_at' => DB::raw('tanggal_upload')]);
    }

    public function down(): void
    {
        Schema::table('materis', function (Blueprint $table) {
            $table->dropColumn(['file', 'published_at']);
        });
    }
};
