<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReserveTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reserve_type', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type',10);
            $table->string('description',30);
            $table->timestamps();
            $table->unique('type');
        });


        DB::table('reserve_type')->insert(
            array(
                'type' => 'REG',
                'description' => 'Regulating'
            )
        );  


        DB::table('reserve_type')->insert(
            array(
                'type' => 'CON',
                'description' => 'Contingency'
            )
        );  

        DB::table('reserve_type')->insert(
            array(
                'type' => 'DIS',
                'description' => 'Dispatchable'
            )
        );  


        DB::table('reserve_type')->insert(
            array(
                'type' => 'RPS',
                'description' => 'Reactive Power Support'
            )
        );  

         DB::table('reserve_type')->insert(
            array(
                'type' => 'BS',
                'description' => 'Black Start'
            )
        );  


         DB::table('reserve_type')->insert(
            array(
                'type' => 'ILD',
                'description' => 'Uninterruptable Load Data'
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
        Schema::dropIfExists('reserve_type');
    }
}
