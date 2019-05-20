<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNominationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nominations', function (Blueprint $table) {
            $table->increments('id');
            $table->date('date');
            $table->text('remarks');
            $table->timestamps();
            $table->integer('submitted_by')->unsigned();
            $table->integer('resources_id')->unsigned();
            $table->integer('customers_id')->unsigned();
            $table->foreign('resources_id')->references('id')->on('resources');
            $table->foreign('customers_id')->references('id')->on('customers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('nominations');
    }
}
