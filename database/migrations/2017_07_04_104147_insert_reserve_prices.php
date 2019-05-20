<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertReservePrices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $areas = array();
        $areas[] = array('node_id' => 'LUZONR' , 'area_type' => 'RA');
        $areas[] = array('node_id' => 'MINDANAOR' , 'area_type' => 'RA');
        $areas[] = array('node_id' => 'VISAYASR' , 'area_type' => 'RA');

        $reserve_classes = array('DIS','CON','REG');
        foreach ($areas as $area) {
            $date = '2017-06-20';
            $node_id = $area['node_id'];
            $area_type = $area['area_type'];
            

            foreach ($reserve_classes as $reserve_class) {
                
                for ($hr=1;$hr<=24;$hr++){
                    $price = rand(0,4000);
                    DB::table('mms_reserve_rtd_prices')->insert(
                        array(
                            'delivery_date' => $date,
                            'delivery_hour' => $hr,
                            'node_id' => $node_id,
                            'area_type' => $area_type,
                            'reserve_class' => $reserve_class,
                            'price' => $price,
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
