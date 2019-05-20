<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterResourceLookupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::dropIfExists('resources_lookups');

        Schema::dropIfExists('resource_lookup');

        Schema::create('resource_lookup', function (Blueprint $table) {
            $table->increments('id');
            $table->string('resource_id',30);
            $table->string('region',30);
            $table->string('type',10);
            $table->boolean('is_mms_reserve');
            $table->string('reserve_classes',100)->nullable();
            $table->timestamps();
            $table->unique(['resource_id'],'resource_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('resource_lookup');
    }
}
