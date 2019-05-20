<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterDummyMmsRtdSchedulese extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mms_rtd_schedules', function (Blueprint $table) {
            $table->dropColumn(['participant_id', 'resource_id']);
            $table->dropUnique('unique_key');
        });


        Schema::table('mms_rtd_schedules', function (Blueprint $table) {
            $table->string('participant_id',10)->after('interval');
            $table->string('resource_id',15)->after('participant_id');

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
