<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("phone")->unique();
            $table->string("email")->unique();
            $table->string("address")->default("")->nullable(true);
            $table->enum("level", ["Silver", "Gold", "Platinum"])->default("Silver");
            $table->string("image");
            $table->string("fcm")->default("");
            $table->integer("status_delete")->default(0);
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
        Schema::dropIfExists('customers');
    }
}
