<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertReserveSchedules extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
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
            $reserve_class = 'DIS';

            for ($hr=1;$hr<=24;$hr++){

                $time = '00:00:01';
                $mw = rand(0,4000);
                DB::table('mms_reserve_rtd_schedules')->insert(
                    array(
                        'delivery_date' => $date,
                        'delivery_hour' => $hr,
                        'interval' => $time,
                        'participant_id' => $participant_id,
                        'resource_id' => $resource_id,
                        'type_id' => $type,
                        'reserve_class' => $reserve_class,
                        'mw' => $mw,
                        'date_posted' => '2017-06-20 10:57:46'
                    )
                );
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
