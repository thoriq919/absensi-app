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
        Schema::table('cutis', function (Blueprint $table) {
            if (!Schema::hasColumn('cutis', 'dokumen_pendukung')) {
                $table->string('dokumen_pendukung')->nullable()->after('keterangan');
            }
            if (Schema::hasColumn('cutis', 'keterangan')) {
                $table->enum('keterangan', ['izin', 'sakit'])->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cutis', function (Blueprint $table) {
            $table->dropColumn('dokumen_pendukung');
            $table->string('keterangan')->change();
        });
    }
};
