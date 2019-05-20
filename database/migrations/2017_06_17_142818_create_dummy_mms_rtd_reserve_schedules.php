<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDummyMmsRtdReserveSchedules extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mms_reserve_rtd_schedules', function (Blueprint $table) {
            $table->increments('id');
            $table->date('delivery_date');
            $table->tinyInteger('delivery_hour')->unsigned();
            $table->time('interval')->nullable();
            $table->string('participant_id',45);
            $table->string('resource_id',45);
            $table->string('type_id',10);
            $table->string('reserve_class',10);
            $table->decimal('mw')->nullable();
            $table->dateTime('date_posted');
            $table->timestamps();
            $table->unique(['delivery_date', 'delivery_hour','participant_id','resource_id','type_id','interval','reserve_class'],'unique_key');
            $table->foreign('reserve_class')->references('type')->on('aspa_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mms_reserve_rtd_schedules');
    }
}
