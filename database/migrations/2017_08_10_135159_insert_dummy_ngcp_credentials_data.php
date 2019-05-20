<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertDummyNgcpCredentialsData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('ngcp_credentials')->insert(
            array(
                'username' => 'tao_tapgc01',
                'password' => 'H@p.L3m0n',
                'plant_name' => 'TAPGC',
                'ngcp_plant_name' => 'TRANS-ASIA',
                'status' => 'active'
            )
        );

        DB::table('ngcp_credentials')->insert(
            array(
                'username' => 'tao_ospgc01',
                'password' => 'S@f@ri.69',
                'plant_name' => 'OSPGC',
                'ngcp_plant_name' => 'SUBIC',
                'status' => 'active'
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
