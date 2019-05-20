<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNominationItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nomination_items', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyinteger('hour');
            $table->time('interval')->nullable();
            $table->decimal('nomination', 20, 10);
            $table->timestamps();
            $table->integer('nominations_id')->unsigned();
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
        Schema::dropIfExists('nomination_items');
    }
}
