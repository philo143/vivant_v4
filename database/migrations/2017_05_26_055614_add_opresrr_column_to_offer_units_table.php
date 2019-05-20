<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOpresrrColumnToOfferUnitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('offer_submission_units', function (Blueprint $table) {
            $table->decimal('opres_ramp_rate',10,5)->after('day_type')->nullable();            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('offer_submission_units', function (Blueprint $table) {
            $table->dropColumn('opres_ramp_rate');        
        });
    }
}
