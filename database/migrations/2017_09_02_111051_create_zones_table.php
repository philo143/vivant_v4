<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateZonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zones', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('zone_prefix');
            $table->string('zone');
            $table->string('zone_name');
            $table->string('load_name');
            $table->timestamps();
        });

        $zones = array(
            array(
                'zone_prefix' => 1,
                'zone'  => 'North Luzon',
                'zone_name' => 'NORT',
                'load_name' => 'NORT_LG'
            ),
            array(
                'zone_prefix' => 2,
                'zone'  => 'Metro Manila',
                'zone_name' => 'METR',
                'load_name' => 'METR_LG'
            ),
            array(
                'zone_prefix' => 3,
                'zone'  => 'South Luzon',
                'zone_name' => 'SOUT',
                'load_name' => 'SOUT_LG'
            ),
            array(
                'zone_prefix' => 4,
                'zone'  => 'Leyte',
                'zone_name' => 'LEYT',
                'load_name' => 'LEYT_LG'
            ),
            array(
                'zone_prefix' => 5,
                'zone'  => 'Cebu',
                'zone_name' => 'CEBU',
                'load_name' => 'CEBU_LG'
            ),
            array(
                'zone_prefix' => 6,
                'zone'  => 'Negros',
                'zone_name' => 'NEGR',
                'load_name' => 'NEGR_LG'
            ),
            array(
                'zone_prefix' => 7,
                'zone'  => 'Bohol',
                'zone_name' => 'BOHO',
                'load_name' => 'BOHO_LG'
            ),
            array(
                'zone_prefix' => 8,
                'zone'  => 'Panay',
                'zone_name' => 'PANA',
                'load_name' => 'PANA_LG'
            ),
            array(
                'zone_prefix' => 9,
                'zone'  => 'Mindanao',
                'zone_name' => 'MIND',
                'load_name' => 'MIND_LG'
            ),
        );
        foreach($zones as $zone){
            DB::table('zones')->insert(
                $zone
            );
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('zones');
    }
}
