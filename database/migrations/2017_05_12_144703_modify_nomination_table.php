<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyNominationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('nominations', function (Blueprint $table) {
            $table->integer('participants_id')->unsigned();
            $table->foreign('participants_id')->references('id')->on('participants');
            $table->dropForeign(['resources_id']);
            $table->dropColumn('resources_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('nominations', function (Blueprint $table) {
            $table->integer('resources_id')->unsigned();
            $table->foreign('resources_id')->references('id')->on('resources');
            $table->dropForeign(['participants_id']);
            $table->dropColumn('participants_id');
        });
    }
}
