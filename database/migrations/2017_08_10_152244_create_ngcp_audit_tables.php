<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNgcpAuditTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('ngcp_capabilities_submission_audit', function (Blueprint $table) {
            $table->increments('id');
            $table->string('action',10);
            $table->text('data');
            $table->string('user');
            $table->timestamps();
        });

         Schema::create('ngcp_nominations_submission_audit', function (Blueprint $table) {
            $table->increments('id');
            $table->string('action',10);
            $table->text('data');
            $table->string('user');
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
        Schema::dropIfExists('ngcp_capabilities_submission_audit');
        Schema::dropIfExists('ngcp_nominations_submission_audit');
    }
}
