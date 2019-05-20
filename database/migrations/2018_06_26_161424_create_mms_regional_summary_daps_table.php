<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMmsRegionalSummaryDapsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
   public function up()
    {
        Schema::create('mms_regional_summary_dap', function (Blueprint $table) {
            $table->increments('id');
            $table->datetime('run_time');
            $table->datetime('interval_end');
            $table->string('region');
            $table->string('commodity');
            $table->string('scenario')->nullable();
            $table->decimal('commodity_req',20,12)->nullable();
            $table->decimal('bid_in_demand',20,12)->nullable();
            $table->decimal('curtailed_load',20,12)->nullable();
            $table->decimal('energy_loss',20,12)->nullable();
            $table->decimal('generation',20,12)->nullable();
            $table->decimal('import',20,12)->nullable();
            $table->decimal('export',20,12)->nullable();
            $table->unique(array('run_time','interval_end','region','commodity'),'IDX1');
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
         Schema::dropIfExists('mms_regional_summary_dap');
    }
}
