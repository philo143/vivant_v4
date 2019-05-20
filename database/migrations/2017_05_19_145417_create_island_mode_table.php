<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIslandModeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('island_mode', function (Blueprint $table) {
            $table->increments('id');
            $table->date('date');
            $table->tinyInteger('hour')->unsigned();
            $table->time('interval')->nullable();
            $table->integer('plant_id')->unsigned();
            $table->boolean('im');
            $table->integer('submitted_by')->unsigned();
            $table->timestamps();
            $table->foreign('plant_id')->references('id')->on('plants');
            $table->unique(array('date', 'hour','interval','plant_id'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('island_mode');
    }
}
