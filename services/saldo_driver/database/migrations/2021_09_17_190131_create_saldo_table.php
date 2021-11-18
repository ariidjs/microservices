<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSaldoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('saldos', function (Blueprint $table) {
            $table->id();
            $table->biginteger('id_driver');
            $table->string('namabank');
            $table->string('nama');
            $table->string('norek');
            $table->double('saldo');
            $table->enum('type',["withdraw","deposit"]);
            $table->enum('status',["pending","success","failed"])->default("pending"); //1 pending 2 success 3 gagal
            $table->string('image');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('saldos');
    }
}
