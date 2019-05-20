<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWidgetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('widgets', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('description');
            $table->enum('with_resources',[0,1])->default(0);
            $table->timestamps();
        });
        DB::table('widgets')->insert(
            array(
                array('name'=>'Nodal Prices Ticker','description'=>'shows the Realtime Nodal Prices for all GEN/LOAD resource ID\'s this shows current available nodal prices from MMS','with_resources'=>'0'),
                array('name'=>'RTD Grid','description'=>'shows in larger text/font the values of the RTD per Resource ID','with_resources'=>'1'),
                array('name'=>'System Messages','description'=>'shows System Messages','with_resources'=>'0'),
                array('name'=>'RTEM Bids and Offers','description'=>'shows the Current Offers submitted for the current trading date','with_resources'=>'1'),
                array('name'=>'Nodal Prices','description'=>'shows the Nodal Prices of your Resource ID\'s','with_resources'=>'1'),
                array('name'=>'Weather','description'=>'show the weather of various location','with_resources'=>'0'),
                array('name'=>'Twitter','description'=>'shows Twitter','with_resources'=>'0'),
                array('name'=>'Daily Market Demand','description'=>'Displays Day Ahead and Realtime Daily Market Demand data per region','with_resources'=>'0'),
                array('name'=>'LWAP','description'=>'Displays Day Ahead and Realtime LWAP data per region','with_resources'=>'0'),
                array('name'=>'Reserve Schedules, NGCP AS & Dispatched Capacity','description'=>'shows the Reserve Schedules, NGCP AS and Dispatch Capacity','with_resources'=>'0'),
                array('name'=>'DAP Reserve Projections (Price)','description'=>'shows Day Ahead Reserve Projections (Price)','with_resources'=>'1'),
                array('name'=>'DAP Reserve Projections (Schedule)','description'=>'shows Day Ahead Reserve Projections (Schedule)','with_resources'=>'1'),
                array('name'=>'Actual Load','description'=>'shows Actual Load of each Resource ID','with_resources'=>'1'),
                array('name'=>'NGCP ASPA AS vs ASPA Dispatched Schedules','description'=>'shows NGCP ASPA Approved Schedules vs ASPA DIspatched Scehdules','with_resources'=>'1'),
                array('name'=>'MMS Reserve Sched vs NGCP ASPA AS','description'=>'shows MMS Reserve Sched vs NGCP ASPA Approved Sched','with_resources'=>'1'),
                array('name'=>'NGCP ASPA AS for Current Interval','description'=>'shows NGCP ASPA APproved Sched for Current Interval','with_resources'=>'1')
            )
        );
    }   

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('widgets');
    }
}
