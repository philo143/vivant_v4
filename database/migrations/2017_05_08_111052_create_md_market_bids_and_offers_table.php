<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMdMarketBidsAndOffersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('md_market_bids_and_offers', function (Blueprint $table) {
            $table->increments('id');
            $table->date('date');
            $table->tinyInteger('hour')->unsigned();
            $table->time('interval')->nullable();
            $table->char('report', 4);
            $table->string('region', 10);
            $table->char('type', 4);
            $table->string('participant', 10);
            $table->string('resource_id', 20);
            $table->decimal('q1',10,4)->nullable();
            $table->decimal('p1',10,4)->nullable();
            $table->decimal('q2',10,4)->nullable();
            $table->decimal('p2',10,4)->nullable();
            $table->decimal('q3',10,4)->nullable();
            $table->decimal('p3',10,4)->nullable();
            $table->decimal('q4',10,4)->nullable();
            $table->decimal('p4',10,4)->nullable();
            $table->decimal('q5',10,4)->nullable();
            $table->decimal('p5',10,4)->nullable();
            $table->decimal('q6',10,4)->nullable();
            $table->decimal('p6',10,4)->nullable();
            $table->decimal('q7',10,4)->nullable();
            $table->decimal('p7',10,4)->nullable();
            $table->decimal('q8',10,4)->nullable();
            $table->decimal('p8',10,4)->nullable();
            $table->decimal('q9',10,4)->nullable();
            $table->decimal('p9',10,4)->nullable();
            $table->decimal('q10',10,4)->nullable();
            $table->decimal('p10',10,4)->nullable();
            $table->decimal('q11',10,4)->nullable();
            $table->decimal('p11',10,4)->nullable();
            $table->decimal('rr_break_qty1',10,4)->nullable();
            $table->decimal('rr_up1',10,4)->nullable();
            $table->decimal('rr_down1',10,4)->nullable();
            $table->decimal('rr_break_qty2',10,4)->nullable();
            $table->decimal('rr_up2',10,4)->nullable();
            $table->decimal('rr_down2',10,4)->nullable();
            $table->decimal('rr_break_qty3',10,4)->nullable();
            $table->decimal('rr_up3',10,4)->nullable();
            $table->decimal('rr_down3',10,4)->nullable();
            $table->decimal('rr_break_qty4',10,4)->nullable();
            $table->decimal('rr_up4',10,4)->nullable();
            $table->decimal('rr_down4',10,4)->nullable();
            $table->decimal('rr_break_qty5',10,4)->nullable();
            $table->decimal('rr_up5',10,4)->nullable();
            $table->decimal('rr_down5',10,4)->nullable();
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
        Schema::dropIfExists('mb_market_bids_and_offers');
    }
}
