<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSeinTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sein', function (Blueprint $table) {
            $table->increments('id');
            $table->string('sein', 45);
            $table->enum('type',['GEN','LD']);
            $table->integer('customers_id')->unsigned()->nullable();
            $table->integer('resources_id')->unsigned()->nullable();
            $table->timestamps();
            $table->foreign('customers_id')->references('id')->on('customers');
            $table->foreign('resources_id')->references('id')->on('resources');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sein');
    }
}
