<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CleanUpWidgetsData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('user_widgets')->truncate();
        DB::table('role_widgets')->truncate();
        DB::table('widgets')->truncate();

        DB::table('widgets')->insert(
            array(
                array('name'=>'Nodal Prices Ticker','description'=>'shows the Realtime Nodal Prices for all GEN/LOAD resource ID\'s this shows current available nodal prices from MMS','with_resources'=>'0','is_active' => 1),
                array('name'=>'RTD Grid','description'=>'shows in larger text/font the values of the RTD per Resource ID','with_resources'=>'1','is_active' => 1),
                array('name'=>'Nodal Prices','description'=>'shows the Nodal Prices of your Resource ID\'s','with_resources'=>'1','is_active' => 1),
                array('name'=>'Weather','description'=>'show the weather of various location','with_resources'=>'0','is_active' => 1),
                array('name'=>'Twitter','description'=>'shows Twitter','with_resources'=>'0','is_active' => 1),
                array('name'=>'Actual Load','description'=>'shows Actual Load of each Resource ID','with_resources'=>'1','is_active' => 1),
                array('name'=>'HAP Prices','description'=>'Hour Ahead Projections Prices','with_resources'=>'1','is_active' => 1),
                array('name'=>'HAP Prices and Schedules','description'=>'Hour Ahead Projections Prices and Schedules','with_resources'=>'1','is_active' => 1),
                 array('name'=>'DAP Prices','description'=>'shows the Day Ahead Projections Prices','with_resources'=>'1','is_active' => 1),
                array('name'=>'DAP Schedules','description'=>'Day Ahead Projections Schedules','with_resources'=>'1','is_active' => 1),
                array('name'=>'DAP Prices and Schedules','description'=>'Day Ahead Projections Prices and Schedules','with_resources'=>'1','is_active' => 1)
            )
        );

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
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
