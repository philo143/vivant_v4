<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertDummyMmsData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        

        ##########################################################################################
        ### MMS RTD PRICES ###
        DB::table('mms_rtd_prices')->delete();
        $resources = array();
        $resources[] = array('resource_id' => '1BAUANG_G01' , 'type' => 'GEN', 'participant_id' => '1590EC');
        $resources[] = array('resource_id' => '1ANGAT_A' , 'type' => 'GEN', 'participant_id' => 'AHC');
        $resources[] = array('resource_id' => '1ANGAT_M' , 'type' => 'GEN', 'participant_id' => 'AHC');
        $resources[] = array('resource_id' => '1S_ENRO_G01' , 'type' => 'GEN', 'participant_id' => 'OSPGC');
        $resources[] = array('resource_id' => '8GUIM_G01' , 'type' => 'GEN', 'participant_id' => 'PHEN');
        $resources[] = array('resource_id' => '1AMBUK_U01' , 'type' => 'GEN', 'participant_id' => 'SNAPBENGT');
        $resources[] = array('resource_id' => '1AMBUK_U02' , 'type' => 'GEN', 'participant_id' => 'SNAPBENGT');
        $resources[] = array('resource_id' => '1AMBUK_U03' , 'type' => 'GEN', 'participant_id' => 'SNAPBENGT');


        $resources[] = array('resource_id' => '1AEC_L01' , 'type' => 'LD', 'participant_id' => 'WESMTMP');
        $resources[] = array('resource_id' => '1AMBUK_SS' , 'type' => 'LD', 'participant_id' => 'WESM');
        $resources[] = array('resource_id' => '1ANGAT_M' , 'type' => 'LD', 'participant_id' => 'WESM');
        $resources[] = array('resource_id' => '1BAKUN_SS' , 'type' => 'LD', 'participant_id' => 'WESM');



        foreach ($resources as $resource) {
            $date = '2017-06-20';
            $resource_id = $resource['resource_id'];
            $type = $resource['type'];
            $participant_id = $resource['participant_id'];

            for ($hr=1;$hr<=24;$hr++){
                for ($intra=5;$intra<=60;$intra+=5){
                    $hr_prefix = $hr-1;

                    if ($intra == 60 ) {
                        $hr_prefix = $hr;

                        $time = str_pad($hr_prefix,2,"0",STR_PAD_LEFT) . ':00:00';
                    }else {
                        $time = str_pad($hr_prefix,2,"0",STR_PAD_LEFT) . ':' . str_pad($intra,2,"0",STR_PAD_LEFT) . ':00';
                    }
                    
                    $price = rand(0,4000);
                    DB::table('mms_rtd_prices')->insert(
                        array(
                            'delivery_date' => $date,
                            'delivery_hour' => $hr,
                            'interval' => $time,
                            'participant_id' => $participant_id,
                            'resource_id' => $resource_id,
                            'type_id' => $type,
                            'price' => $price
                        )
                    );


                }
            }

        }


        ##########################################################################################
        ### MMS RTD SCHEDULES ###
        DB::table('mms_rtd_schedules')->delete();
        $resources = array();
        $resources[] = array('resource_id' => '1CIP2_G01' , 'type' => 'GEN', 'participant_id' => 'CIP2');
        $resources[] = array('resource_id' => '3MGPP_G01' , 'type' => 'GEN', 'participant_id' => 'MGI');
        $resources[] = array('resource_id' => '1S_ENRO_G01' , 'type' => 'GEN', 'participant_id' => 'OSPGC');
        $resources[] = array('resource_id' => '3SLTEC_G01' , 'type' => 'GEN', 'participant_id' => 'SLTEC');
        $resources[] = array('resource_id' => '3SLTEC_G02' , 'type' => 'GEN', 'participant_id' => 'SLTEC');
        $resources[] = array('resource_id' => '1T_ASIA_G01' , 'type' => 'GEN', 'participant_id' => 'TAPGC');



        foreach ($resources as $resource) {
            $date = '2017-06-20';
            $resource_id = $resource['resource_id'];
            $type = $resource['type'];
            $participant_id = $resource['participant_id'];

            for ($hr=1;$hr<=24;$hr++){

                for ($intra=5;$intra<=60;$intra+=5){
                    $hr_prefix = $hr-1;

                    if ($intra == 60 ) {
                        $hr_prefix = $hr;

                        $time = str_pad($hr_prefix,2,"0",STR_PAD_LEFT) . ':00:00';
                    }else {
                        $time = str_pad($hr_prefix,2,"0",STR_PAD_LEFT) . ':' . str_pad($intra,2,"0",STR_PAD_LEFT) . ':00';
                    }

                    $mw = rand(0,4000);
                    DB::table('mms_rtd_schedules')->insert(
                        array(
                            'delivery_date' => $date,
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
            }

        }






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
