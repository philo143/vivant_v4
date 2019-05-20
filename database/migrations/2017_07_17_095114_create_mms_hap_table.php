<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMmsHapTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mms_hap_prices_and_schedules', function (Blueprint $table) {
            $table->increments('id');
            $table->date('delivery_date');
            $table->tinyInteger('delivery_hour')->unsigned();
            $table->time('interval')->nullable();
            $table->string('resource_id',45);
            $table->decimal('mw')->nullable();
            $table->decimal('price')->nullable();
            $table->timestamps();
            $table->unique(['delivery_date', 'delivery_hour','interval','resource_id'],'unique_key');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mms_hap_prices_and_schedules');
    }
}
