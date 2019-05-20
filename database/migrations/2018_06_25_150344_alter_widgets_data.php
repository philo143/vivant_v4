<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterWidgetsData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('widgets', function (Blueprint $table) {
            $table->boolean('is_active')->nullable()->default(1);
        });

        DB::table('widgets')->where('name', 'System Messages')->update(['is_active' => 0]);
        DB::table('widgets')->where('name', 'RTEM Bids and Offers')->update(['is_active' => 0]);
        DB::table('widgets')->where('name', 'Daily Market Demand')->update(['is_active' => 0]);
        DB::table('widgets')->where('name', 'LWAP')->update(['is_active' => 0]);
        DB::table('widgets')->where('name', 'Reserve Schedules, NGCP AS & Dispatched Capacity')->update(['is_active' => 0]);
        DB::table('widgets')->where('name', 'DAP Reserve Projections (Price)')->update(['is_active' => 0]);
        DB::table('widgets')->where('name', 'DAP Reserve Projections (Schedule)')->update(['is_active' => 0]);
        DB::table('widgets')->where('name', 'NGCP ASPA AS vs ASPA Dispatched Schedules')->update(['is_active' => 0]);
        DB::table('widgets')->where('name', 'MMS Reserve Sched vs NGCP ASPA AS')->update(['is_active' => 0]);
        DB::table('widgets')->where('name', 'NGCP ASPA AS for Current Interval')->update(['is_active' => 0]);


        DB::table('widgets')->insert(
            array(
                array('name'=>'HAP Prices','description'=>'shows the Hour Ahead Projections Prices','with_resources'=>'1','is_active' => 1),
                array('name'=>'HAP Schedules','description'=>'Hour Ahead Projections Schedules','with_resources'=>'1','is_active' => 0),
                array('name'=>'HAP Prices and Schedules','description'=>'Hour Ahead Projections Prices and Schedules','with_resources'=>'1','is_active' => 1),
                 array('name'=>'DAP Prices','description'=>'shows the Day Ahead Projections Prices','with_resources'=>'1','is_active' => 1),
                array('name'=>'DAP Schedules','description'=>'Day Ahead Projections Schedules','with_resources'=>'1','is_active' => 1),
                array('name'=>'DAP Prices and Schedules','description'=>'Day Ahead Projections Prices and Schedules','with_resources'=>'1','is_active' => 1)
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
        Schema::table('widgets', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
}
