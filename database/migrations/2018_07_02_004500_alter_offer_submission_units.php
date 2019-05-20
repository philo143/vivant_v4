<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterOfferSubmissionUnits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('offer_submission_units', function (Blueprint $table) {
            $table->integer('participants_id')->after('delivery_date');
            $table->dropColumn('status');
            
        });
        Schema::table('offer_submission_units', function (Blueprint $table) {
            $table->string('status')->after('response_trans_id');
            
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
            $table->dropColumn('participant');
            $table->dropColumn('status');
        });
        Schema::table('offer_submission_units', function (Blueprint $table) {
            $table->enum('status',[1,0])->after('response_trans_id');; 
        });
    }
}
