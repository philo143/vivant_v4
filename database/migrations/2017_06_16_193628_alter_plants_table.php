<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterPlantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('plants', function (Blueprint $table) {
            $table->boolean('is_aspa')->after('description')->default(0);
            $table->string('aspa_type',10)->after('is_aspa')->nullable();
            $table->boolean('is_island_mode')->after('aspa_type')->default(0);

            $table->foreign('aspa_type')->references('type')->on('aspa_type');
            $table->unique(['plant_name'],'plant_name');
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
