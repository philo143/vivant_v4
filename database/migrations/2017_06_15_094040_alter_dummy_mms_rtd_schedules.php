<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterDummyMmsRtdSchedules extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mms_rtd_schedules', function (Blueprint $table) {
            $table->dropColumn(['type_id']);
            $table->dropUnique('unique_key');
        });


        Schema::table('mms_rtd_schedules', function (Blueprint $table) {
            $table->string('type_id',10)->after('resource_id');

             $table->unique(['delivery_date', 'delivery_hour','resource_id','type_id','interval'],'unique_key');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
