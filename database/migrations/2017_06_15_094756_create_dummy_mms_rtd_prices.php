<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDummyMmsRtdPrices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('mms_rtd_prices', function (Blueprint $table) {
            $table->increments('id');
            $table->date('delivery_date');
            $table->tinyInteger('delivery_hour')->unsigned();
            $table->time('interval')->nullable();
            $table->string('participant_id',10);
            $table->string('resource_id',15);
            $table->string('type_id',10);
            $table->decimal('price')->nullable();
            $table->timestamps();
            $table->unique(['delivery_date', 'delivery_hour','resource_id','type_id','interval'],'unique_key');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mms_rtd_prices');
    }
}
