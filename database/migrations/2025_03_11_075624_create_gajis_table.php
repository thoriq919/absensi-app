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
        Schema::create('gajis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('karyawan_id');
            $table->date('tanggal_gaji'); 
            $table->decimal('gaji_pokok', 15, 2)->default(1200000); 
            $table->decimal('tunjangan_kehadiran', 15, 2); 
            $table->decimal('lembur', 15, 2); 
            $table->decimal('potongan', 15, 2); 
            $table->decimal('gaji_bersih', 15, 2); 
            $table->boolean('validated')->default(false); 
            $table->timestamps();

            $table->foreign('karyawan_id')->references('id')->on('karyawans')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gajis');
    }
};
