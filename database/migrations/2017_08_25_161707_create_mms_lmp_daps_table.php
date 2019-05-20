<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMmsLmpDapsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mms_lmp_dap', function (Blueprint $table) {
            $table->increments('id');
            $table->datetime('run_time');
            $table->datetime('interval_end');
            $table->string('price_node');
            $table->decimal('lmp',20,12);
            $table->unique(array('run_time','interval_end','price_node'));
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
         Schema::dropIfExists('mms_lmp_dap');
    }
}
