<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateResourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('resources', function (Blueprint $table) {
            $table->increments('id');
            $table->string('resource_id',30);
            $table->integer('plant_id');
            $table->enum('region',['luzon','visayas','mindanao']);
            $table->decimal('pmin',10,5);
            $table->decimal('pmax',10,5);
            $table->decimal('ramp_rate',10,5);
            $table->decimal('ramp_up',10,5);
            $table->decimal('ramp_down',10,5);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('resources');
    }
}
