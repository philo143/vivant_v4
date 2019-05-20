<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerNominationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_nomination', function (Blueprint $table) {
            $table->integer('customers_id')->unsigned();
            $table->integer('nominations_id')->unsigned();
            $table->foreign('customers_id')->references('id')->on('customers');
            $table->foreign('nominations_id')->references('id')->on('nominations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_nomination');
    }
}
