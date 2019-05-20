<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyPlantCapabilityTypeTableAddDescription extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('plant_capability_type', function (Blueprint $table) {
            $table->string('description',50)->after('type');
        });


        DB::table('plant_capability_type')->where('type','RT')->update(['description'=>'Realtime']);
        DB::table('plant_capability_type')->where('type','DAP')->update(['description'=>'Day Ahead']);
        DB::table('plant_capability_type')->where('type','WAP')->update(['description'=>'Week Ahead']);
           
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('plant_capability_type', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
}
