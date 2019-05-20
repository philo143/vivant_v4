<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMmsRtemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mms_rtem', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('resources_id')->unsigned();
            $table->date('date');
            $table->tinyInteger('hour');
            $table->time('interval')->nullable();
            for($i=1;$i<=11;$i++){
                $table->decimal('price'.$i,10,5)->nullable();
                $table->decimal('qty'.$i,10,5)->nullable();
            }
            for($i=1;$i<=5;$i++){
                $table->decimal('breakpoint'.$i,10,5)->nullable();
                $table->decimal('ramp_up'.$i,10,5)->nullable();
                $table->decimal('ramp_down'.$i,10,5)->nullable();
            }
            $table->text('reason');         
            $table->timestamps();
            $table->foreign('resources_id')->references('id')->on('resources');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mms_rtem');
    }
}
