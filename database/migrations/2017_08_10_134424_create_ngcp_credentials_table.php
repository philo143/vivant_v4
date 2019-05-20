<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNgcpCredentialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ngcp_credentials', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username',255)->nullable();
            $table->string('password',255)->nullable();
            $table->string('plant_name',255)->nullable();
            $table->string('ngcp_plant_name',255)->nullable();
            $table->enum('status', ['active', 'inactive'])->nullable();
            $table->string('created_by')->nullable();
            $table->timestamps();
            $table->unique(['username', 'plant_name','ngcp_plant_name'],'indx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ngcp_credentials');
    }
}
