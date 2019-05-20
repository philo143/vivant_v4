<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTradingShiftReportTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trading_shift_report_type', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type',10); 
            $table->string('description',20); 
        });


        Schema::create('trading_shift_report', function (Blueprint $table) {
            $table->increments('id');
            $table->date('date');
            $table->tinyInteger('hour')->unsigned();
            $table->time('interval')->nullable();
            $table->integer('type_id')->unsigned();
            $table->text('report')->nullable();
            $table->integer('submitted_by')->unsigned();
            $table->timestamps();
            $table->foreign('type_id')->references('id')->on('trading_shift_report_type');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trading_shift_report_type');
        Schema::dropIfExists('trading_shift_report');
    }
}
