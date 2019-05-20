<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertPermissionData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        ### Permission Table
        DB::table('permissions')->insert(
                array(
                    'id' => '1',
                    'name' => 'users-create',
                    'display_name' => 'Create Users',
                    'description' => 'Create Users'
                )
            );  


        DB::table('permissions')->insert(
                array(
                    'id' => '2',
                    'name' => 'users-read',
                    'display_name' => 'Read Users',
                    'description' => 'Read Users'
                )
            );  


         DB::table('permissions')->insert(
                array(
                    'id' => '3',
                    'name' => 'users-update',
                    'display_name' => 'Update Users',
                    'description' => 'Update Users'
                )
            );  

         DB::table('permissions')->insert(
                array(
                    'id' => '4',
                    'name' => 'users-delete',
                    'display_name' => 'Delete Users',
                    'description' => 'Delete Users'
                )
            );  


         DB::table('permissions')->insert(
                array(
                    'id' => '5',
                    'name' => 'acl-create',
                    'display_name' => 'Create Acl',
                    'description' => 'Create Acl'
                )
            );  


         DB::table('permissions')->insert(
                array(
                    'id' => '6',
                    'name' => 'acl-read',
                    'display_name' => 'Read Acl',
                    'description' => 'Read Acl'
                )
            );  


         DB::table('permissions')->insert(
                array(
                    'id' => '7',
                    'name' => 'acl-update',
                    'display_name' => 'Update Acl',
                    'description' => 'Update Acl'
                )
            );  


         DB::table('permissions')->insert(
                array(
                    'id' => '8',
                    'name' => 'acl-delete',
                    'display_name' => 'Delete Acl',
                    'description' => 'Delete Acl'
                )
            );  


          DB::table('permissions')->insert(
                array(
                    'id' => '9',
                    'name' => 'profile-read',
                    'display_name' => 'Read Profile',
                    'description' => 'Read Profile'
                )
            );  


          DB::table('permissions')->insert(
                array(
                    'id' => '10',
                    'name' => 'profile-update',
                    'display_name' => 'Update Profile',
                    'description' => 'Update Profile'
                )
            );  


          for ($role_id=1;$role_id<=2;$role_id++){
                
                for ($ii=1;$ii<=10;$ii++){
                    DB::table('permission_role')->insert(
                        array(
                            'permission_id' => $ii,
                            'role_id' => $role_id
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
