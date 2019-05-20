<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCustomerParticipantTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_participant', function (Blueprint $table) {
            $table->integer('customers_id')->unsigned();
            $table->integer('participants_id')->unsigned();
            $table->foreign('customers_id')->references('id')->on('customers');
            $table->foreign('participants_id')->references('id')->on('participants');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_participant');
    }
}
