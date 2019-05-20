<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDummyNgcpSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('ngcp_schedules', function (Blueprint $table) {
            $table->increments('id');
            $table->date('date');
            $table->string('plant',30);
            $table->string('unit_no',30);
            $table->string('reserve_class',10);
            

            for($i=1;$i<=24;$i++){
                $table->decimal('hour'.$i,20,12)->nullable();
            }

            $table->timestamps();
            $table->unique(['date', 'plant','unit_no','reserve_class'],'unique_key');
            $table->foreign('reserve_class')->references('type')->on('aspa_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ngcp_schedules');
    }
}
