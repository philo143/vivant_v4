<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Carbon\Carbon;


class InsertDummyDataForNgcpTablesSanmigDemo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        

        ### resources
        $resources = array();
        $resources[] = array(
            'resource_id' => '1SROQUE_U01',
            'plant_id' => 3,
            'unit_no' => 1,
            'type' => 'GEN', 
            'participant_id' => 'SPDC'
        );


        $start_date = Carbon::createFromTimestamp(strtotime(''))->format('Y-m-d');
        $end_date = Carbon::createFromTimestamp(strtotime('2017-09-10'))->format('Y-m-d');
        $start_date = date("Y-m-d", strtotime("2017-08-31") );
        $end_date = date("Y-m-d", strtotime("2017-09-10") );
        $date = $start_date;
        $my_end_date = $end_date;
        $days_ctr = 1;
        while (strtotime($date) <= strtotime($my_end_date)) {
            $dte = date('Y-m-d',strtotime($date));

            foreach ($resources as $row) {
                $resource_id = $row['resource_id'];
                $plant_id = $row['plant_id'];
                $participant_id = $row['participant_id'];
                $type = $row['type'];
                $unit_no = 'Unit ' . $row['unit_no'];


                $data = array(
                        'date' => $dte,
                        'plant' => $participant_id,
                        'unit_no' => $unit_no,
                        'reserve_type' => 'DIS'
                    );

                $data2 = array(
                        'date' => $dte,
                        'plant' => $participant_id,
                        'unit_no' => $unit_no,
                        'reserve_class' => 'DIS'
                    );

                for ($hr=1;$hr<=24;$hr++){
                    $data['hour'.$hr] = rand(0,100);
                    $data2['hour'.$hr] = rand(0,100);
                }   

                DB::table('ngcp_capabilities')->insert(
                    $data
                );

                DB::table('ngcp_nomination_prices')->insert(
                    $data2
                );

                DB::table('ngcp_nominations')->insert(
                    $data
                );

                 DB::table('ngcp_schedules')->insert(
                    $data2
                );



            }



            



            $date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
            $days_ctr++;
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
