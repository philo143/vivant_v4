<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class OfferAudit extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offer_audit', function (Blueprint $table) {
            $table->increments('id');
            $table->string('transaction_id');
            $table->longText('data');
            $table->date('delivery_date');
            $table->string('resource_id');
            $table->string('type');
            $table->integer('submitted_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('offer_audit');
    }
}
