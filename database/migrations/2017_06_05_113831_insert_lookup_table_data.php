<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertLookupTableData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
            

          ## Plant Capability Type  
          DB::table('plant_capability_type')->insert(
                array(
                    'id' => '1',
                    'type' => 'RT'
                )
            );  


          DB::table('plant_capability_type')->insert(
                array(
                    'id' => '2',
                    'type' => 'DAP'
                )
            );  


         DB::table('plant_capability_type')->insert(
                array(
                    'id' => '3',
                    'type' => 'WAP'
                )
            );   



         ### Plant Shift Report Type
         DB::table('plant_shift_report_type')->insert(
                array(
                    'id' => '1',
                    'type' => 'activity',
                    'description' => 'Activity'
                )
            );  

         DB::table('plant_shift_report_type')->insert(
                array(
                    'id' => '2',
                    'type' => 'audit',
                    'description' => 'Audit'
                )
            );  


         DB::table('plant_shift_report_type')->insert(
                array(
                    'id' => '3',
                    'type' => 'rtd',
                    'description' => 'RTD'
                )
            );  


        
        ### Trading Shift Report Type
        DB::table('trading_shift_report_type')->insert(
                array(
                    'id' => '1',
                    'type' => 'activity',
                    'description' => 'Activity'
                )
            );  

         DB::table('trading_shift_report_type')->insert(
                array(
                    'id' => '2',
                    'type' => 'actual',
                    'description' => 'Actual Load'
                )
            );  


         DB::table('trading_shift_report_type')->insert(
                array(
                    'id' => '3',
                    'type' => 'audit',
                    'description' => 'Audit'
                )
            );   



         ### Plant Capability Status
         DB::table('plant_capability_status')->insert(
                array(
                    'id' => '1',
                    'status' => 'Available'
                )
            );   


         DB::table('plant_capability_status')->insert(
                array(
                    'id' => '2',
                    'status' => 'Scheduled Outage'
                )
            );   


          DB::table('plant_capability_status')->insert(
                array(
                    'id' => '3',
                    'status' => 'Unscheduled Outage'
                )
            );   

          DB::table('plant_capability_status')->insert(
                array(
                    'id' => '4',
                    'status' => 'MO Intervention'
                )
            );   


          DB::table('plant_capability_status')->insert(
                array(
                    'id' => '5',
                    'status' => 'SO Intervention'
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
        //
    }
}
