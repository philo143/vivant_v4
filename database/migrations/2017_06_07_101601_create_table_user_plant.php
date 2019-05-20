<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableUserPlant extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_plant', function (Blueprint $table) {
            $table->integer('users_id')->unsigned();
            $table->integer('plants_id')->unsigned();
            $table->foreign('users_id')->references('id')->on('users');
            $table->foreign('plants_id')->references('id')->on('plants');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_plant');
    }
}
