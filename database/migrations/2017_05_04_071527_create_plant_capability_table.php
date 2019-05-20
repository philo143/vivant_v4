<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlantCapabilityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plant_capability_status', function (Blueprint $table) {
            $table->increments('id');
            $table->string('status'); 
        });
        Schema::create('plant_capability_type', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('type', ['WAP','DAP','RT']); 
        });
        Schema::create('plant_capability', function (Blueprint $table) {
            $table->increments('id');
            $table->date('delivery_date');
            $table->tinyInteger('hour');
            $table->time('interval')->nullable();
            $table->decimal('capability',10,5);
            $table->text('description')->nullable();
            $table->timestamps();        
            $table->integer('resources_id')->unsigned();
            $table->integer('plant_capability_type_id')->unsigned();
            $table->integer('plant_capability_status_id')->unsigned();
            $table->foreign('resources_id')->references('id')->on('resources');
            $table->foreign('plant_capability_type_id')->references('id')->on('plant_capability_type');
            $table->foreign('plant_capability_status_id')->references('id')->on('plant_capability_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('plant_capability_status');
        Schema::dropIfExists('plant_capability_type');
        Schema::dropIfExists('plant_capability');
    }
}
