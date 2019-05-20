<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDummyMmsSystemMessages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mms_system_messages', function (Blueprint $table) {
            $table->increments('id');
            $table->dateTime('date');
            $table->string('urgency',10);
            $table->text('message');
            $table->timestamps();
            $table->unique(['date'],'unique_key');
            $table->index(['date','urgency'],'key');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mms_system_messages');
    }
}
