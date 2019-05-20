<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRTPMActualLoadAcknowledgeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rtpm_actual_load_acknowledge', function (Blueprint $table) {
            $table->increments('id');
            $table->date('date');
            $table->tinyInteger('hour')->unsigned();
            $table->time('interval')->nullable();
            $table->integer('plant_id')->unsigned();
            $table->integer('resource_id')->unsigned();
            $table->decimal('actual_load',20,10)->nullable();
            $table->boolean('actual_load_acknowledged')->default(0);
            $table->integer('actual_load_acknowledged_by')->unsigned()->nullable();
            $table->dateTime('actual_load_acknowledged_dt')->nullable();
            $table->boolean('rtd_acknowledged')->default(0);
            $table->decimal('rtd',20,10)->nullable();
            $table->integer('rtd_acknowledged_by')->unsigned()->nullable();
            $table->dateTime('rtd_acknowledged_dt')->nullable();
            $table->integer('submitted_by')->unsigned();
            $table->timestamps();
            $table->foreign('plant_id')->references('id')->on('plants');
            $table->foreign('resource_id')->references('id')->on('resources');
            $table->unique(array('date', 'hour','interval','plant_id','resource_id'),'unique_key');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rtpm_actual_load_acknowledge');
    }
}
