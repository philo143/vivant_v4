<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Carbon\Carbon;

class InsertDummyDataForSanmigDemo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Eloquent::unguard();

        //disable foreign key check for this connection before running seeders
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        DB::table('aspa_nominations')->truncate();
        DB::table('mms_dap_prices_and_schedules')->truncate();
        DB::table('mms_hap_prices_and_schedules')->truncate();
        DB::table('mms_reserve_rtd_prices')->truncate();
        DB::table('mms_reserve_rtd_schedules')->truncate();
        DB::table('mms_rtd_prices')->truncate();
        DB::table('mms_rtd_schedules')->truncate();
        DB::table('mms_system_messages')->truncate();
        DB::table('ngcp_capabilities')->truncate();
        DB::table('ngcp_capabilities_submission_audit')->truncate();
        DB::table('ngcp_nomination_prices')->truncate();
        DB::table('ngcp_nominations')->truncate();
        DB::table('ngcp_nominations_submission_audit')->truncate();
        DB::table('ngcp_schedules')->truncate();
        DB::table('plant_capability')->truncate();
        DB::table('plant_capability_audit')->truncate();
        DB::table('plant_shift_report')->truncate();
        
        DB::table('rtpm_actual_load_acknowledge')->truncate();
        DB::table('trading_shift_report')->truncate();
        DB::table('user_plant')->truncate();
        DB::table('user_resource')->truncate();
        DB::table('island_mode')->truncate();
        DB::table('island_mode')->truncate();

        DB::table('participants')->truncate();
        DB::table('plants')->truncate();
        DB::table('resources')->truncate();
        DB::table('user_widgets')->truncate();

        ## Data for Participants
        DB::table('participants')->insert(
            array(
                'participant_name' => 'SMEC',
                'description' => 'SMEC'
            )
        );

        DB::table('participants')->insert(
            array(
                'participant_name' => 'SPPC',
                'description' => 'SPPC'
            )
        );

        DB::table('participants')->insert(
            array(
                'participant_name' => 'SPDC',
                'description' => 'SPDC'
            )
        );


        ## Data for plants
        DB::table('plants')->insert(
            array(
                'participant_id' => '1',
                'plant_name' => 'SMEC',
                'long_name' => 'SMEC',
                'location' => '',
                'description' => '',
                'is_aspa' => 0,
                'is_island_mode' => 0,
                'engines' => 1
            )
        ); 

        DB::table('plants')->insert(
            array(
                'participant_id' => '2',
                'plant_name' => 'SPPC',
                'long_name' => 'SPPC',
                'location' => '',
                'description' => '',
                'is_aspa' => 0,
                'is_island_mode' => 0,
                'engines' => 1
            )
        ); 

        DB::table('plants')->insert(
            array(
                'participant_id' => '3',
                'plant_name' => 'SPDC',
                'long_name' => 'SPDC',
                'location' => '',
                'description' => '',
                'is_aspa' => 0,
                'is_island_mode' => 0,
                'engines' => 1
            )
        ); 


        ### resources
        $resources = array();
        $resources[] = array(
                'resource_id' => '1SUAL_G01',
                'plant_id' => 1,
                'unit_no' => 1,
                'type' => 'GEN', 
                'participant_id' => 'SMEC'
            );

        $resources[] = array(
                'resource_id' => '1SUAL_G02',
                'plant_id' => 1,
                'unit_no' => 2,
                'type' => 'GEN', 
                'participant_id' => 'SMEC'
            );


        $resources[] = array(
                'resource_id' => '3ILIJAN_G01',
                'plant_id' => 2,
                'unit_no' => 1,
                'type' => 'GEN', 
                'participant_id' => 'SPPC'
            );

        $resources[] = array(
                'resource_id' => '3ILIJAN_G02',
                'plant_id' => 2,
                'unit_no' => 2,
                'type' => 'GEN', 
                'participant_id' => 'SPPC'
            );


        $resources[] = array(
                'resource_id' => '1SROQUE_U01',
                'plant_id' => 3,
                'unit_no' => 1,
                'type' => 'GEN', 
                'participant_id' => 'SPDC'
            );

        $resources[] = array(
                'resource_id' => '1SROQUE_U02',
                'plant_id' => 3,
                'unit_no' => 2,
                'type' => 'GEN', 
                'participant_id' => 'SPDC'
            );

        $resources[] = array(
                'resource_id' => '1SROQUE_U03',
                'plant_id' => 3,
                'unit_no' => 3,
                'type' => 'GEN', 
                'participant_id' => 'SPDC'
            );

        foreach ($resources as $row) {
            DB::table('resources')->insert(
                array(
                    'resource_id' => $row['resource_id'],
                    'plant_id' => $row['plant_id'],
                    'region' => 'luzon',
                    'pmin' => 0,
                    'pmax' => 0,
                    'ramp_rate' => 0,
                    'ramp_up' => 0,
                    'ramp_down' => 1,
                    'unit_no' => $row['unit_no']
                )
            ); 
        }




        $start_date = Carbon::createFromTimestamp(strtotime(''))->format('Y-m-d');
        $end_date = Carbon::createFromTimestamp(strtotime('2017-09-10'))->format('Y-m-d');
        $start_date = date("Y-m-d", strtotime("2017-08-31") );
        $end_date = date("Y-m-d", strtotime("2017-09-10") );
        $date = $start_date;
        $my_end_date = $end_date;
        $days_ctr = 1;
        while (strtotime($date) <= strtotime($my_end_date)) {
            $dte = date('Y-m-d',strtotime($date));

            ## Dummy data for dap prices and sched
            foreach ($resources as $row) {
                $resource_id = $row['resource_id'];
                $plant_id = $row['plant_id'];
                $participant_id = $row['participant_id'];
                $type = $row['type'];

                for ($hr=1;$hr<=24;$hr++){

                    $mw = rand(0,100);
                    $price = rand(1000,4000);

                    DB::table('mms_dap_prices_and_schedules')->insert(
                        array(
                            'delivery_date' => $dte,
                            'delivery_hour' => $hr,
                            'resource_id' => $resource_id,
                            'mw' => $mw,
                            'price' => $price,
                        )
                    );



                    if ($resource_id == '1SROQUE_U01') {
                        $time = '00:00:01';
                        DB::table('mms_reserve_rtd_schedules')->insert(
                            array(
                                'delivery_date' => $dte,
                                'delivery_hour' => $hr,
                                'interval' => $time,
                                'participant_id' => $participant_id,
                                'resource_id' => $resource_id,
                                'type_id' => $type,
                                'reserve_class' => 'DIS',
                                'mw' => $mw,
                                'date_posted' => '2017-06-20 10:57:46'
                            )
                        );


                        DB::table('mms_reserve_rtd_prices')->insert(
                            array(
                                'delivery_date' => $dte,
                                'delivery_hour' => $hr,
                                'node_id' => 'LUZONR',
                                'area_type' => 'RA',
                                'reserve_class' => 'DIS',
                                'price' => $price,
                                'date_posted' => '2017-06-20 10:57:46'
                            )
                        );

                    }

                    // intra interval loop
                    for ($intra=5;$intra<=60;$intra+=5){
                        $hr_prefix = $hr-1;

                        if ($intra == 60 ) {
                            $hr_prefix = $hr;

                            $time = str_pad($hr_prefix,2,"0",STR_PAD_LEFT) . ':00:00';
                        }else {
                            $time = str_pad($hr_prefix,2,"0",STR_PAD_LEFT) . ':' . str_pad($intra,2,"0",STR_PAD_LEFT) . ':00';
                        }

                        $mw = rand(0,100);
                        $price = rand(100,4000);

                        DB::table('mms_hap_prices_and_schedules')->insert(
                            array(
                                'delivery_date' => $dte,
                                'delivery_hour' => $hr,
                                'interval' => $time,
                                'resource_id' => $resource_id,
                                'mw' => $mw,
                                'price' => $price,
                            )
                        );


                        DB::table('mms_rtd_prices')->insert(
                            array(
                                'delivery_date' => $dte,
                                'delivery_hour' => $hr,
                                'interval' => $time,
                                'participant_id' => $participant_id,
                                'resource_id' => $resource_id,
                                'type_id' => $type,
                                'price' => $price
                            )
                        );


                        DB::table('mms_rtd_schedules')->insert(
                            array(
                                'delivery_date' => $dte,
                                'delivery_hour' => $hr,
                                'interval' => $time,
                                'participant_id' => $participant_id,
                                'resource_id' => $resource_id,
                                'type_id' => $type,
                                'mw' => $mw,
                                'date_posted' => '2017-06-20 10:57:46'
                            )
                        );


                    }
                    // intra 





                } // for interval

            }



            



            $date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
            $days_ctr++;
        }



        


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
