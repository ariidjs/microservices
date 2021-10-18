<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('notransaksi');
            $table->biginteger('id_customer');
            $table->biginteger('id_driver');
            $table->biginteger('id_store');
            $table->integer('status');
            $table->string('total_price');
            $table->string('driver_price');
            $table->string('alamat_user');
            $table->string('latitude');
            $table->string('longitude');
            $table->string('kode_validasi');
            $table->integer('status_delete');
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
        Schema::dropIfExists('transactions');
    }
}
