<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id("id_store");
            $table->string("owner_name");
            $table->string("store_name");
            $table->string("phone")->unique();
            $table->string("email")->unique();
            $table->string("fcm")->default("");
            $table->integer("status_store")->default(0);
            $table->integer("status_delete")->default(0);
            $table->string("description_store");
            $table->double("saldo")->default(0);
            $table->string("role")->default('store');
            $table->string("nik_ktp");
            $table->double("rating")->default(5.0);
            $table->string("photo_ktp");
            $table->string("photo_store");
            $table->string("latitude");
            $table->string("longititude");
            $table->string("address");
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
        Schema::dropIfExists('stores');
    }
}
