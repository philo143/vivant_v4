<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertAcaciaUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        ## Roles
        DB::table('roles')->insert(
            array(
                'id' => '1',
                'name' => 'superadministrator',
                'display_name' => 'Superadministrator',
                'description' => 'Superadministrator'
            )
        );


        DB::table('roles')->insert(
            array(
                'id' => '2',
                'name' => 'administrator',
                'display_name' => 'Administrator',
                'description' => 'Administrator'
            )
        );


        DB::table('roles')->insert(
            array(
                'id' => '3',
                'name' => 'user',
                'display_name' => 'User',
                'description' => 'User'
            )
        );


        DB::table('roles')->insert(
            array(
                'id' => '4',
                'name' => 'trader',
                'display_name' => 'Trader',
                'description' => 'Trader'
            )
        );


        // acacia users for insert
        $list = array();
        $list[] = array(
            'username' => 'akel',
            'email' => 'akel.alix@acacia-soft.com',
            'password' => '$2y$10$Km7wg8G8rjDtaf6woKrIyuWjSVLIyoJaWBFIQCGQbdtKINh4Cy6M2',
            'mobile' => '707-617-5275 x650',
            'status' => 1,
            'fullname' => 'Akel Alix'
        );


        $list[] = array(
            'username' => 'glen',
            'email' => 'glenford.lim@acacia-soft.com',
            'password' => '$2y$10$Km7wg8G8rjDtaf6woKrIyuWjSVLIyoJaWBFIQCGQbdtKINh4Cy6M2',
            'mobile' => '707-617-5275 x650',
            'status' => 1,
            'fullname' => 'Glenford Lim'
        );


        $list[] = array(
            'username' => 'rjay',
            'email' => 'ryan.gregorio@acacia-soft.com',
            'password' => '$2y$10$Km7wg8G8rjDtaf6woKrIyuWjSVLIyoJaWBFIQCGQbdtKINh4Cy6M2',
            'mobile' => '707-617-5275 x650',
            'status' => 1,
            'fullname' => 'Ryan Gregorio'
        );


        $list[] = array(
            'username' => 'brian',
            'email' => 'brian.lim@acacia-soft.com',
            'password' => '$2y$10$Km7wg8G8rjDtaf6woKrIyuWjSVLIyoJaWBFIQCGQbdtKINh4Cy6M2',
            'mobile' => '707-617-5275 x650',
            'status' => 1,
            'fullname' => 'Brian Lim'
        );

        $list[] = array(
            'username' => 'xtian',
            'email' => 'christian.amonoy@acacia-soft.com',
            'password' => '$2y$10$Km7wg8G8rjDtaf6woKrIyuWjSVLIyoJaWBFIQCGQbdtKINh4Cy6M2',
            'mobile' => '707-617-5275 x650',
            'status' => 1,
            'fullname' => 'Christian Amonoy'
        );

        $list[] = array(
            'username' => 'philo',
            'email' => 'phil.catanyag@acacia-soft.com',
            'password' => 'e10adc3949ba59abbe56e057f20f883e',
            'mobile' => '707-617-5275 x650',
            'status' => 1,
            'fullname' => 'Phil Catanyag'
        );


        foreach ($list as $params) {
            
            $id = DB::table('users')->insertGetId($params);

            DB::table('role_user')->insert(
                array(
                    'user_id' => $id,
                    'role_id' => 1
                )
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
        //
    }
}
