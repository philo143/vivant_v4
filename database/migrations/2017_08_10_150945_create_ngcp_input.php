<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNgcpInput extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ngcp_input', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('lfpdsr')->nullable();
            $table->integer('lnpdsr')->nullable();
            $table->integer('vfpdsr')->nullable();
            $table->integer('vnpdsr')->nullable();
            $table->integer('mfpdsr')->nullable();
            $table->integer('mnpdsr')->nullable();
            $table->integer('firm1')->nullable();
            $table->integer('nonfirm1')->nullable();
            $table->integer('lncmc')->nullable();

            for($i=1;$i<=5;$i++){
                $table->integer('mc'.$i)->nullable();
            }

            for($i=1;$i<=5;$i++){
                $table->integer('uc'.$i)->nullable();
            }

            for($i=1;$i<=5;$i++){
                $table->integer('lfasc'.$i)->nullable();
            }

            for($i=1;$i<=5;$i++){
                $table->integer('lnasc'.$i)->nullable();
            }

            for($i=1;$i<=5;$i++){
                $table->integer('vfasc'.$i)->nullable();
            }

            for($i=1;$i<=5;$i++){
                $table->integer('vnasc'.$i)->nullable();
            }

            for($i=1;$i<=5;$i++){
                $table->integer('mlasc'.$i)->nullable();
            }

            for($i=1;$i<=5;$i++){
                $table->integer('mnasc'.$i)->nullable();
            }

            $table->string('year',25);
            $table->string('billing_period',100);
            $table->string('month_name',25);
            $table->string('month',25);
            $table->date('date');
            $table->integer('lf')->nullable();
            $table->integer('lnf')->nullable();
            $table->integer('vf2')->nullable();
            $table->integer('vnf2')->nullable();
            $table->integer('mf3')->nullable();
            $table->integer('mnf3')->nullable();
            $table->string('other_rates',25);
            $table->integer('other1')->nullable();
            $table->integer('other2')->nullable();
            $table->timestamps();
            // $table->unique(['date', 'plant','unit_no','reserve_type'],'unique_key');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ngcp_input');
    }
}
