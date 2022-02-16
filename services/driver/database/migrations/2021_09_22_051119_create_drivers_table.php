<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDriversTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->string("name_driver");
            $table->string("email")->unique();
            $table->string("phone")->unique();
            $table->string("nomor_stnk")->unique();
            $table->string("plat_kendaraan")->unique();
            $table->string("nik")->unique();
            $table->string("photo_profile");
            $table->string("photo_stnk");
            $table->string("photo_ktp");
            $table->smallInteger("j_kelamin");
            $table->double("rating")->default(5.0);
            $table->double("saldo")->default(0);
            $table->integer('total_order')->default(0);
            $table->integer("status")->default(0);
            $table->integer("status_delete")->default(0);
            $table->string("fcm")->default('');
            $table->integer("total_rating")->default(1);
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
        Schema::dropIfExists('drivers');
    }
}
