<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyCertLocColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->text('description')->nullable()->change();
            $table->string('cert_loc',100)->nullable()->change();
            $table->string('cert_file',45)->nullable()->change();
            $table->string('cert_user',45)->nullable()->change();
            $table->string('cert_pass',45)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->text('description')->change();
            $table->string('cert_loc',100)->change();
            $table->string('cert_file',45)->change();
            $table->string('cert_user',45)->change();
            $table->string('cert_pass',45)->change();
        });
    }
}
