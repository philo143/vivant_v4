<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOffersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offer_type', function (Blueprint $table) {
            $table->increments('id');
            $table->string('offer_type',10); 
        });
        DB::table('offer_type')->insert(
            array(
                array('offer_type'=>'RTEM'),
                array('offer_type'=>'DAP'),
                array('offer_type'=>'SO'),
                array('offer_type'=>'RTEMR'),
                array('offer_type'=>'DAPR'),
                array('offer_type'=>'SOR')
            )
        );
        Schema::create('offer_submission_units', function (Blueprint $table) {
            $table->increments('id');
            $table->date('delivery_date'); 
            $table->date('expiry_date')->nullable();
            $table->string('day_type',10)->nullable();
            $table->enum('status',[1,0]); 
            $table->text('generated_xml')->nullable(); 
            $table->text('response_str')->nullable(); 
            $table->string('response_trans_id',45)->nullable();
            $table->text('offer_params')->nullable();
            $table->enum('action',['submit','cancel'])->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->integer('submitted_by');
            $table->integer('offer_type_id')->unsigned();
            $table->integer('resources_id')->unsigned();
            $table->foreign('offer_type_id')->references('id')->on('offer_type');
            $table->foreign('resources_id')->references('id')->on('resources');
        });
        Schema::create('offer_submission_data', function (Blueprint $table) {
            $table->increments('id');
            $table->date('delivery_date'); 
            $table->tinyInteger('hour'); 
            $table->time('interval')->nullable(); 
            for($i=0;$i<=10;$i++){
                $table->decimal('b_p'.$i,20,12)->nullable();
                $table->decimal('b_v'.$i,20,12)->nullable();
            }
            for($i=0;$i<=4;$i++){
                $table->decimal('breakpoint'.$i,20,12)->nullable();
                $table->decimal('ramp_up'.$i,20,12)->nullable();
                $table->decimal('ramp_down'.$i,20,12)->nullable();
            }
            $table->text('remarks')->nullable();
            $table->enum('go_status',['-1','0','1','2'])->nullable();
            $table->string('return_code')->nullable();
            $table->timestamps();
            $table->integer('submitted_by');
            $table->integer('offer_submission_units_id')->unsigned();
            $table->foreign('offer_submission_units_id')->references('id')->on('offer_submission_units');
        });
        Schema::create('offer_reserve_data', function (Blueprint $table) {
            $table->increments('id');
            $table->string('reserve_class');
            $table->date('date'); 
            $table->tinyInteger('hour'); 
            $table->time('interval')->nullable(); 
            for($i=1;$i<=11;$i++){
                $table->decimal('price'.$i,25,12)->nullable();
                $table->decimal('qty'.$i,25,12)->nullable();
            }
            $table->decimal('ramp_rate',25,12)->nullable();            
            $table->text('reason')->nullable();
            $table->timestamps();
            $table->integer('offer_submission_units_id')->unsigned();
            $table->foreign('offer_submission_units_id')->references('id')->on('offer_submission_units');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('offer_submission_units');
        Schema::dropIfExists('offer_submission_data');
        Schema::dropIfExists('offer_reserve_data');
        Schema::dropIfExists('offer_type');
        Schema::dropIfExists('offer_wesm_responses');
    }
}
