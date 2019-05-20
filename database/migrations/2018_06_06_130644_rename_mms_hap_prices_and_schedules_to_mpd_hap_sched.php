<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameMmsHapPricesAndSchedulesToMpdHapSched extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::rename('mms_hap_prices_and_schedules', 'mms_mpd_hap_sched');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::rename('mms_mpd_hap_sched','mms_hap_prices_and_schedules');
    }
}
