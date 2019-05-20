<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\User;

class SanMiguelDataSeeder extends Seeder
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

        DB::table('participants')->insert(
            array(
                'participant_name' => 'SMCCPC',
                'description' => 'SMCCPC'
            )
        );

        DB::table('participants')->insert(
            array(
                'participant_name' => 'MPPC',
                'description' => 'MPPC'
            )
        );


        ## Data for plants
        DB::table('plants')->insert(
            array(
            	array(
	                'participant_id' => '1',
	                'plant_name' => 'SMEC',
	                'long_name' => 'San Miguel Energy Corporation',
	                'location' => 'Sual, Pangasinan',
	                'description' => 'PULVERIZED COAL PLANT',
	                'is_aspa' => 0,
	                'is_island_mode' => 0,
	                'engines' => 1
	            ),
	            array(
	                'participant_id' => '2',
	                'plant_name' => 'SPPC',
	                'long_name' => 'South Premier Power Corporation',
	                'location' => 'Ilijan, Batangas',
	                'description' => 'COMBINED CYCLE POWER PLANT',
	                'is_aspa' => 0,
	                'is_island_mode' => 0,
	                'engines' => 1
	            ),
	            array(
	                'participant_id' => '3',
	                'plant_name' => 'SPDC',
	                'long_name' => 'Strategic Power Development Corporation',
	                'location' => 'San Manuel, Pangasinan',
	                'description' => 'MULTIPURPOSE HYDRO ELECTRIC PLANT',
	                'is_aspa' => 0,
	                'is_island_mode' => 0,
	                'engines' => 1
	            ),
	            array(
	                'participant_id' => '4',
	                'plant_name' => 'SMCCPC',
	                'long_name' => 'SMC Consolidated Power',
	                'location' => 'Limay, Bataan',
	                'description' => 'CFB COAL PLANT',
	                'is_aspa' => 0,
	                'is_island_mode' => 0,
	                'engines' => 1
	            ),
	            array(
	                'participant_id' => '5',
	                'plant_name' => 'MPPCL',
	                'long_name' => 'MASINLOC POWER PARTNERS CO. LTD.',
	                'location' => 'BRGY. BANI, MASINLOC, ZAMBALES',
	                'description' => 'PULVERIZED COAL PLANT',
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
	                'resource_id' => '01SUAL_G01',
	                'plant_id' => 1,
	                'region' => 'LUZON',
	                'pmin' => 225,
	                'pmax' => 647,
	                'ramp_rate' => 5,
	                'ramp_up' => 5,
	                'ramp_down' => 5,
	                'unit_no' => 1
	            ),
	            array(
	                'resource_id' => '01SUAL_G02',
	                'plant_id' => 1,
	                'region' => 'LUZON',
	                'pmin' => 225,
	                'pmax' => 647,
	                'ramp_rate' => 5,
	                'ramp_up' => 5,
	                'ramp_down' => 5,
	                'unit_no' => 2
	            ),
	            array(
	                'resource_id' => '03ILIJAN_G01',
	                'plant_id' => 2,
	                'region' => 'LUZON',
	                'pmin' => 300,
	                'pmax' => 600,
	                'ramp_rate' => 16,
	                'ramp_up' => 16,
	                'ramp_down' => 16,
	                'unit_no' => 1
	            ),
	            array(
	                'resource_id' => '03ILIJAN_G02',
	                'plant_id' => 2,
	                'region' => 'LUZON',
	                'pmin' => 300,
	                'pmax' => 600,
	                'ramp_rate' => 16,
	                'ramp_up' => 16,
	                'ramp_down' => 16,
	                'unit_no' => 2
	            ),
	            array(
	                'resource_id' => '01SROQUE_U01',
	                'plant_id' => 3,
	                'region' => 'LUZON',
	                'pmin' => 0,
	                'pmax' => 145,
	                'ramp_rate' => 16,
	                'ramp_up' => 16,
	                'ramp_down' => 16,
	                'unit_no' => 1
	            ),
	            array(
	                'resource_id' => '01SROQUE_U02',
	                'plant_id' => 3,
	                'region' => 'LUZON',
	                'pmin' => 0,
	                'pmax' => 145,
	                'ramp_rate' => 16,
	                'ramp_up' => 16,
	                'ramp_down' => 16,
	                'unit_no' => 2
	            ),
	            array(
	                'resource_id' => '01SROQUE_U03',
	                'plant_id' => 3,
	                'region' => 'LUZON',
	                'pmin' => 0,
	                'pmax' => 145,
	                'ramp_rate' => 16,
	                'ramp_up' => 16,
	                'ramp_down' => 16,
	                'unit_no' => 3
	            )
	            ,
	            array(
	                'resource_id' => '01SMC_G01',
	                'plant_id' => 4,
	                'region' => 'LUZON',
	                'pmin' => 60,
	                'pmax' => 150,
	                'ramp_rate' => 1,
	                'ramp_up' => 1,
	                'ramp_down' => 1,
	                'unit_no' => 1
	            ),
	            array(
	                'resource_id' => '01SMC_G02',
	                'plant_id' => 4,
	                'region' => 'LUZON',
	                'pmin' => 60,
	                'pmax' => 150,
	                'ramp_rate' => 1,
	                'ramp_up' => 1,
	                'ramp_down' => 1,
	                'unit_no' => 2
	            ),
	            array(
	                'resource_id' => '01SMC_G03',
	                'plant_id' => 4,
	                'region' => 'LUZON',
	                'pmin' => 60,
	                'pmax' => 150,
	                'ramp_rate' => 1,
	                'ramp_up' => 1,
	                'ramp_down' => 1,
	                'unit_no' => 3
	            ),
	            array(
	                'resource_id' => '01MSINLO_G01',
	                'plant_id' => 5,
	                'region' => 'LUZON',
	                'pmin' => 100,
	                'pmax' => 315,
	                'ramp_rate' => 4,
	                'ramp_up' => 4,
	                'ramp_down' => 4,
	                'unit_no' => 1
	            ),
	            array(
	                'resource_id' => '01MSINLO_G02',
	                'plant_id' => 5,
	                'region' => 'LUZON',
	                'pmin' => 100,
	                'pmax' => 344,
	                'ramp_rate' => 4,
	                'ramp_up' => 4,
	                'ramp_down' => 4,
	                'unit_no' => 2
	            )
            )
        ); 


		DB::table('users')->insert(
            array(
            	array(
	                'username' => 'cleviste',
	                'fullname' => 'Cristy Leviste',
	                'email' => 'cleviste@smcgph.sanmiguel.com.ph',
	                'password' => bcrypt('Csmda170!'),
	                'mobile' => '702-4617',
	                'status' => 1
	            ),
	            array(
	                'username' => 'jraymundo',
	                'fullname' => 'Jose Ferlino	Raymundo',
	                'email' => 'jraymundo@smcgph.sanmiguel.com.ph',
	                'password' => bcrypt('Csmuc358!'),
	                'mobile' => '702-4616',
	                'status' => 1
	            ),
	            array(
	                'username' => 'gabrenica',
	                'fullname' => 'Genny Rose Abrenica',
	                'email' => 'gabrenica@smcgph.sanmiguel.com.ph',
	                'password' => bcrypt('Csqdc391!'),
	                'mobile' => '702-4614',
	                'status' => 1
	            ),
	            array(
	                'username' => 'dllanes',
	                'fullname' => 'Daryl Andrew	Llanes',
	                'email' => 'dllanes@smcgph.sanmiguel.com.ph',
	                'password' => bcrypt('Cqprc556!'),
	                'mobile' => '702-4613',
	                'status' => 1
	            ),
	            array(
	                'username' => 'ccariso',
	                'fullname' => 'Cherielyn Cariso',
	                'email' => 'trading@mppcl.sanmiguel.com',
	                'password' => bcrypt('Cmppa271!'),
	                'mobile' => '9498818146',
	                'status' => 1
	            )
            )
        ); 

		$user = User::where('email','cleviste@smcgph.sanmiguel.com.ph')->first();
        $user->attachRole(4);

        $user = User::where('email','jraymundo@smcgph.sanmiguel.com.ph')->first();
        $user->attachRole(4);

        $user = User::where('email','gabrenica@smcgph.sanmiguel.com.ph')->first();
        $user->attachRole(4);

        $user = User::where('email','dllanes@smcgph.sanmiguel.com.ph')->first();
        $user->attachRole(4);

        $user = User::where('email','trading@mppcl.sanmiguel.com')->first();
        $user->attachRole(4);

		DB::statement('SET FOREIGN_KEY_CHECKS=1;');

    }
}
