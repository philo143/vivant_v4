<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Carbon\Carbon;
use App\Resource;

class ActualLoadDummy extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $date = Carbon::now()->format('Y-m-d');
        $resources = Resource::orderBy('resource_id', 'asc')->get();
        foreach ($resources as $resource_row) {
            $resource_id = $resource_row->id;
            $plant_id = $resource_row->plant_id;


            for ($hr=1;$hr<=24;$hr++){

                for ($intra=5;$intra<=60;$intra+=5){
                    $hr_prefix = $hr-1;

                    if ($intra == 60 ) {
                        $hr_prefix = $hr;

                        $time = str_pad($hr_prefix,2,"0",STR_PAD_LEFT) . ':00:00';
                        $val = str_pad($hr_prefix,2,"0",STR_PAD_LEFT) . '00';
                    }else {
                        $time = str_pad($hr_prefix,2,"0",STR_PAD_LEFT) . ':' . str_pad($intra,2,"0",STR_PAD_LEFT) . ':00';

                        $val = str_pad($hr_prefix,2,"0",STR_PAD_LEFT) . str_pad($intra,2,"0",STR_PAD_LEFT);
                    }

                    
                    DB::table('rtpm_actual_load_acknowledge')->insert(
                        array(
                            'date' => $date,
                            'hour' => $hr,
                            'interval' => $time,
                            'plant_id' => $plant_id,
                            'resource_id' => $resource_id,
                            'actual_load' => $val,
                            'actual_load_acknowledged_by' => 0,
                            'rtd_acknowledged' => 0,
                            'submitted_by' => 0
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
