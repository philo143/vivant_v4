<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlantShiftReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plant_shift_report_type', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type',10); 
            $table->string('description',20); 
        });


        Schema::create('plant_shift_report', function (Blueprint $table) {
            $table->increments('id');
            $table->date('date');
            $table->tinyInteger('hour')->unsigned();
            $table->time('interval')->nullable();
            $table->integer('plant_id')->unsigned();
            $table->integer('resource_id')->unsigned();
            $table->integer('type_id')->unsigned();
            $table->text('report')->nullable();
            $table->integer('submitted_by')->unsigned();
            $table->timestamps();
            $table->foreign('type_id')->references('id')->on('plant_shift_report_type');
            $table->foreign('plant_id')->references('id')->on('plants');
            $table->foreign('resource_id')->references('id')->on('resources');
            $table->index(['date', 'hour','plant_id','resource_id']);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('plant_shift_report_type');
        Schema::dropIfExists('plant_shift_report');
    }

}
