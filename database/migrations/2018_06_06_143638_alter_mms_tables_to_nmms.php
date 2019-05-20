<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterMmsTablesToNmms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::dropIfExists('mms_mpd_dap_sched');
        Schema::dropIfExists('mms_mpd_hap_sched');
        Schema::create('mms_mpd_hap_sched', function (Blueprint $table) {
            $table->increments('id');
            $table->datetime('run_time');
            $table->datetime('interval_end');
            $table->string('price_node');
            $table->decimal('mw',20,12);
            $table->decimal('lmp',20,12);
            $table->decimal('loss_factor',20,12);
            $table->decimal('energy',20,12);
            $table->decimal('loss',20,12);
            $table->decimal('congestion',20,12);
            $table->unique(array('run_time','interval_end','price_node'));
            $table->timestamps();
        });
        Schema::create('mms_mpd_dap_sched', function (Blueprint $table) {
            $table->increments('id');
            $table->datetime('run_time');
            $table->datetime('interval_end');
            $table->string('price_node');
            $table->decimal('mw',20,12);
            $table->decimal('lmp',20,12);
            $table->decimal('loss_factor',20,12);
            $table->decimal('energy',20,12);
            $table->decimal('loss',20,12);
            $table->decimal('congestion',20,12);
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
        //
    }
}
