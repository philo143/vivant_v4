<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDummyTableMmsRtdReservePrices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        

        Schema::create('mms_reserve_rtd_prices', function (Blueprint $table) {
            $table->increments('id');
            $table->date('delivery_date');
            $table->tinyInteger('delivery_hour')->unsigned();
            $table->string('node_id',15);
            $table->string('area_type',15);
            $table->string('reserve_class',15);
            $table->decimal('price')->nullable();
            $table->dateTime('date_posted');
            $table->timestamps();
            $table->unique(['delivery_date', 'delivery_hour','node_id','area_type','reserve_class'],'unique_key');
            $table->foreign('reserve_class')->references('type')->on('reserve_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mms_reserve_rtd_prices');
    }
}
