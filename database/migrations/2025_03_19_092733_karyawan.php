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
        //
        Schema::create('karyawans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); 
            $table->string('nama'); 
            $table->string('alamat'); 
            $table->string('no_telp'); 
            $table->date('tanggal_masuk'); 
            $table->string('status_karyawan'); 
            $table->string('rfid_number',16)->unique()->nullable(); 
            $table->integer('saldo_cuti')->default(2); 
            $table->boolean('is_active')->default(true); 
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karyawans');
    }
};
