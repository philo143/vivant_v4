<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAspaTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aspa_type', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type',10);
            $table->string('description',30);
            $table->timestamps();
            $table->unique('type');
        });


        DB::table('aspa_type')->insert(
            array(
                'type' => 'REG',
                'description' => 'Regulating Reserve'
            )
        );  


        DB::table('aspa_type')->insert(
            array(
                'type' => 'CON',
                'description' => 'Contingency Reserve'
            )
        );  

        DB::table('aspa_type')->insert(
            array(
                'type' => 'DIS',
                'description' => 'Dispatchable Reserve'
            )
        );  


        DB::table('aspa_type')->insert(
            array(
                'type' => 'REA',
                'description' => 'Reactive Power Support'
            )
        );  

         DB::table('aspa_type')->insert(
            array(
                'type' => 'BLA',
                'description' => 'Black Start'
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
        Schema::dropIfExists('aspa_type');
    }
}
