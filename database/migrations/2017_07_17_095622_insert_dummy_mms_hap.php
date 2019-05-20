<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Carbon\Carbon;

class InsertDummyMmsHap extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $date = Carbon::now()->format('Y-m-d');

        $resources = array('1CIP2_G01','1T_ASIA_G01','1S_ENRO_G01','3MGPP_G01','8SLWIND_G01','1CIP2_G02','1BAUANG_G01','8GUIM_G01','1AMBUK_U01','1AMBUK_U02','1AMBUK_U03');
        

        foreach ($resources as $resource_id) {

            for ($hr=1;$hr<=24;$hr++){

                for ($intra=5;$intra<=60;$intra+=5){
                    $hr_prefix = $hr-1;

                    if ($intra == 60 ) {
                        $hr_prefix = $hr;

                        $time = str_pad($hr_prefix,2,"0",STR_PAD_LEFT) . ':00:00';
                    }else {
                        $time = str_pad($hr_prefix,2,"0",STR_PAD_LEFT) . ':' . str_pad($intra,2,"0",STR_PAD_LEFT) . ':00';
                    }

                    $mw = rand(0,100);
                    $price = rand(1000,4000);

                    DB::table('mms_hap_prices_and_schedules')->insert(
                        array(
                            'delivery_date' => $date,
                            'delivery_hour' => $hr,
                            'interval' => $time,
                            'resource_id' => $resource_id,
                            'mw' => $mw,
                            'price' => $price,
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
