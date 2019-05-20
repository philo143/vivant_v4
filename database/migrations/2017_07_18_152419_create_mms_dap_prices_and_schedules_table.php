<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMmsDapPricesAndSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mms_dap_prices_and_schedules', function (Blueprint $table) {
            $table->increments('id');
            $table->date('delivery_date');
            $table->tinyInteger('delivery_hour')->unsigned();
            $table->string('resource_id',45);
            $table->decimal('mw')->nullable();
            $table->decimal('price')->nullable();
            $table->decimal('loss_factor')->nullable();
            $table->decimal('lmp_energy')->nullable();
            $table->decimal('lmp_loss')->nullable();
            $table->decimal('lmp_congestion')->nullable();
            $table->timestamps();
            $table->unique(['delivery_date', 'delivery_hour','resource_id'],'unique_key');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
        Schema::dropIfExists('mms_dap_prices_and_schedules');
    }
}
