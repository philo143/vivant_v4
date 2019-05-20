<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRoleWidgetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('role_widgets', function (Blueprint $table) {
            $table->integer('roles_id')->unsigned();
            $table->integer('widgets_id')->unsigned();
            $table->foreign('roles_id')->references('id')->on('roles');
            $table->foreign('widgets_id')->references('id')->on('widgets');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('role_widgets');
    }
}
