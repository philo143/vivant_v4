<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDummyPubMpsReserve extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pub_reserve_mps', function (Blueprint $table) {
            $table->increments('id');
            $table->date('delivery_date');
            $table->tinyInteger('hour')->unsigned();
            $table->string('region',15);
            $table->string('reserve_area',15);
            $table->string('reserve_class',15);
            $table->string('type',15);
            $table->string('participant',30);
            $table->string('resource_id',30);
            $table->decimal('mw')->nullable();
            $table->decimal('price')->nullable();
            $table->timestamps();
            $table->unique(['delivery_date', 'hour','region','reserve_class','type','participant','resource_id'],'unique_key');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pub_reserve_mps');
    }
}
