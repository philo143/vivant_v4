<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeNominationsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        Schema::dropIfExists('nominations');
        Schema::dropIfExists('nomination_items');

        // nominations
        Schema::create('nominations', function (Blueprint $table) {
            $table->increments('id');
            $table->date('date');
            $table->enum('type',['DAN','WAN','MAN']);
            $table->integer('participants_id')->unsigned();
            $table->integer('customers_id')->unsigned();
            $table->text('remarks');
            $table->date('sdate')->nullable();
            $table->date('edate')->nullable();
            $table->integer('submitted_by')->unsigned();
            $table->timestamps();
            $table->foreign('participants_id')->references('id')->on('participants');
            $table->foreign('customers_id')->references('id')->on('customers');
            $table->unique(['date','type','participants_id','customers_id'],'unique_key');
        });

        // nomination items
        Schema::create('nomination_items', function (Blueprint $table) {
            $table->increments('id');
            $table->date('date');
            $table->tinyinteger('hour');
            $table->time('interval')->nullable();
            $table->decimal('nomination', 20, 10);
            $table->integer('nominations_id')->unsigned();
            $table->timestamps();
            $table->foreign('nominations_id')->references('id')->on('nominations');
        });

        Schema::create('nominations_audit', function (Blueprint $table) {
            $table->increments('id');
            $table->string('transaction_id',100);
            $table->integer('nominations_id')->unsigned();
            $table->date('sdate')->nullable();
            $table->date('edate')->nullable();
            $table->enum('type',['DAN','WAN','MAN']);
            $table->integer('participants_id')->unsigned();
            $table->integer('customers_id')->unsigned();
            $table->text('data');
            $table->text('remarks');
            $table->integer('submitted_by')->unsigned();
            $table->timestamps();
        });

        DB::statement('SET FOREIGN_KEY_CHECKS = 1');


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        Schema::dropIfExists('nominations');
        Schema::dropIfExists('nomination_items');
        Schema::dropIfExists('nominations_audit');

        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        
    }
}
