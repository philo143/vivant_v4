<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateASPANominationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aspa_nominations', function (Blueprint $table) {
            $table->increments('id');
            $table->date('date');
            $table->tinyInteger('hour')->unsigned();
            $table->time('interval')->nullable();
            $table->integer('plant_id')->unsigned();
            $table->integer('resource_id')->unsigned();
            $table->decimal('dispatch_capacity',20,10)->nullable();
            $table->text('remarks')->nullable();
            $table->integer('submitted_by')->unsigned();
            $table->timestamps();
            $table->foreign('plant_id')->references('id')->on('plants');
            $table->foreign('resource_id')->references('id')->on('resources');
            $table->unique(array('date', 'hour','interval','plant_id','resource_id'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('aspa_nominations');
    }
}
