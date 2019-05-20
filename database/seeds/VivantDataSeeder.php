<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\User;

class VivantDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       
       	Eloquent::unguard();

        //disable foreign key check for this connection before running seeders
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        DB::table('participants')->truncate();
        DB::table('plants')->truncate();
        DB::table('resources')->truncate();
        DB::table('user_widgets')->truncate();
        DB::table('user_plant')->truncate();
        DB::table('user_resource')->truncate();



        ## Data for Participants
        DB::table('participants')->insert(
            array(
                'participant_name' => '1590EC',
                'description' => '1590 Energy Corporation'
            )
        );

        DB::table('participants')->insert(
            array(
                'participant_name' => 'NR',
                'description' => 'Northern Renewables.'
            )
        );

        


        ## Data for plants
        DB::table('plants')->insert(
            array(
            	array(
	                'participant_id' => '1',
	                'plant_name' => '1590EC',
	                'long_name' => '1590 Energy Corp',
	                'location' => 'Km 255 Payocpoc Sur, Bauang, La Union',
	                'description' => '1590 Energy Corp',
	                'is_aspa' => 0,
	                'is_island_mode' => 0,
	                'engines' => 1
	            ),
	            array(
	                'participant_id' => '2',
	                'plant_name' => 'BAKUN',
	                'long_name' => 'BAKUN',
	                'location' => 'Brgy. Amilongan, Alilem, Ilocos Sur.',
	                'description' => 'BAKUN',
	                'is_aspa' => 0,
	                'is_island_mode' => 0,
	                'engines' => 1
	            )
            )
        ); 

        

        ### resources
        DB::table('resources')->insert(
            array(
            	array(
	                'resource_id' => '1BAUANG_G01',
	                'plant_id' => 1,
	                'region' => 'LUZON',
	                'pmin' => 0,
	                'pmax' => 200,
	                'ramp_rate' => 4,
	                'ramp_up' => 2,
	                'ramp_down' => 2,
	                'unit_no' => 1
	            ),
	            array(
	                'resource_id' => '1BAKUN_G01',
	                'plant_id' => 2,
	                'region' => 'LUZON',
	                'pmin' => 1,
	                'pmax' => 75,
	                'ramp_rate' => 75,
	                'ramp_up' => 3,
	                'ramp_down' => 3,
	                'unit_no' => 1
	            )
            )
        ); 


		

		DB::statement('SET FOREIGN_KEY_CHECKS=1;');

    }
}