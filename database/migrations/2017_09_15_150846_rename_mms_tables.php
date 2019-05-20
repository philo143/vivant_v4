<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameMmsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::rename('mms_rtd', 'mms_mod_rtd');
        Schema::rename('mms_lmp', 'mms_mod_lmp');
        Schema::rename('mms_lmp_hap', 'mms_mpd_hap_lmp');
        Schema::rename('mms_lmp_dap', 'mms_mpd_dap_lmp');
        Schema::rename('mms_lmp_wap', 'mms_mpd_wap_lmp');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::rename('mms_mod_rtd', 'mms_rtd');
        Schema::rename('mms_mod_lmp', 'mms_lmp');
        Schema::rename('mms_mpd_hap_lmp', 'mms_lmp_hap');
        Schema::rename('mms_mpd_dap_lmp', 'mms_lmp_dap');
        Schema::rename('mms_mpd_wap_lmp', 'mms_lmp_wap');
    }
}
