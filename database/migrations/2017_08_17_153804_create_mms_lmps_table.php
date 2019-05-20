<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMmsLmpsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mms_lmp', function (Blueprint $table) {
            $table->increments('id');
            $table->date('date');
            $table->time('interval');
            $table->string('price_node');
            $table->decimal('lmp',20,12);
            $table->unique(array('date','interval','price_node'));
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
        Schema::dropIfExists('mms_lmp');
    }
}
