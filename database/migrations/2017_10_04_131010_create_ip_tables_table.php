<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIpTablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ip_tables', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type');
            $table->string('ip_address');
            $table->boolean('status');
            $table->timestamps();
        });


        Schema::create('ip_tables_audit', function (Blueprint $table) {
            $table->increments('id');
            $table->string('action');
            $table->string('data');
            $table->string('user');
            $table->timestamps();
        });


        // insert initial data 
        DB::table('ip_tables')->insert(
            array(
                'type' => 'mms',
                'ip_address' => '203.177.47.91',
                'status' => 1
            )
        );

        DB::table('ip_tables')->insert(
            array(
                'type' => 'wesm',
                'ip_address' => 'wesm.ph',
                'status' => 1
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
        Schema::dropIfExists('ip_tables');
    }
}
