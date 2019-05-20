<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNgcpNominationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('ngcp_nominations', function (Blueprint $table) {
            $table->increments('id');
            $table->date('date');
            $table->string('plant',30);
            $table->string('unit_no',30);
            $table->string('reserve_type',20);
            

            for($i=1;$i<=24;$i++){
                $table->decimal('hour'.$i,30,12)->nullable();
            }

            $table->timestamps();
            $table->unique(['date', 'plant','unit_no','reserve_type'],'unique_key');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ngcp_nominations');
    }
}
