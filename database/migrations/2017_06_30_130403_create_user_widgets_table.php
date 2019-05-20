<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserWidgetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_widgets', function (Blueprint $table) {
            $table->integer('users_id')->unsigned();
            $table->integer('widgets_id')->unsigned();
            $table->integer('resources_id')->unsigned()->nullable();
            $table->foreign('users_id')->references('id')->on('users');
            $table->foreign('widgets_id')->references('id')->on('widgets');
            $table->foreign('resources_id')->references('id')->on('resources');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_widgets');
    }
}
