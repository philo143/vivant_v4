<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDummyResourceLookup extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('resource_lookup');
        
        Schema::create('resource_lookup', function (Blueprint $table) {
            $table->increments('id');
            $table->string('resource_id');
            $table->string('region');
            $table->enum('type',['GEN','LD']); 
            $table->tinyInteger('is_mms_reserve')->default(0); 
            $table->timestamps();
            $table->unique(['resource_id', 'region','type','is_mms_reserve'],'unique_key');
        });


        // dummy data
        DB::table('resource_lookup')->insert(
            array(
                array('resource_id'=>'1AEC_G01', 'region' => 'LUZON' , 'type' => 'GEN'),
                array('resource_id'=>'1AMBUK_U01', 'region' => 'LUZON' , 'type' => 'GEN'),
                array('resource_id'=>'1AMBUK_U02', 'region' => 'LUZON' , 'type' => 'GEN'),
                array('resource_id'=>'1AMBUK_U03', 'region' => 'LUZON' , 'type' => 'GEN'),
                array('resource_id'=>'1ANGAT_A', 'region' => 'LUZON' , 'type' => 'GEN'),
                array('resource_id'=>'1ANGAT_M', 'region' => 'LUZON' , 'type' => 'GEN'),
                array('resource_id'=>'1BAUANG_S01', 'region' => 'LUZON' , 'type' => 'GEN'),
                array('resource_id'=>'1BAUANG_S02', 'region' => 'LUZON' , 'type' => 'GEN'),
                array('resource_id'=>'1BAUANG_S03', 'region' => 'LUZON' , 'type' => 'GEN'),
                array('resource_id'=>'3SLPGC_G01', 'region' => 'LUZON' , 'type' => 'GEN')
            )
        );


        DB::table('resource_lookup')->insert(
            array(
                array('resource_id'=>'8STBAR_SS1', 'region' => 'VISAYAS' , 'type' => 'LD'),
                array('resource_id'=>'8STBAR_SS2', 'region' => 'VISAYAS' , 'type' => 'LD'),
                array('resource_id'=>'3DASMA_T1L1', 'region' => 'LUZON' , 'type' => 'LD'),
                array('resource_id'=>'3DASMA_T1L2', 'region' => 'LUZON' , 'type' => 'LD'),
                array('resource_id'=>'3DASMA_T1L3', 'region' => 'LUZON' , 'type' => 'LD'),
                array('resource_id'=>'3DASMA_T1L4', 'region' => 'LUZON' , 'type' => 'LD'),
                array('resource_id'=>'3DASMA_T1L5', 'region' => 'LUZON' , 'type' => 'LD'),
                array('resource_id'=>'3DASMA_T1L6', 'region' => 'LUZON' , 'type' => 'LD'),
                array('resource_id'=>'3DASMA_T1L7', 'region' => 'LUZON' , 'type' => 'LD'),
                array('resource_id'=>'1MAEC_S01', 'region' => 'LUZON' , 'type' => 'LD')
            )
        );


        DB::table('resource_lookup')->insert(
            array(
                array('resource_id'=>'1S_ENRO_G01', 'region' => 'LUZON' , 'type' => 'GEN' , 'is_mms_reserve' => 1),
                array('resource_id'=>'1MAGAT_U01', 'region' => 'LUZON' , 'type' => 'GEN', 'is_mms_reserve' => 1),
                array('resource_id'=>'1MAGAT_U02', 'region' => 'LUZON' , 'type' => 'GEN', 'is_mms_reserve' => 1),
                array('resource_id'=>'1MAGAT_U03', 'region' => 'LUZON' , 'type' => 'GEN', 'is_mms_reserve' => 1),
                array('resource_id'=>'1MAGAT_U04', 'region' => 'LUZON' , 'type' => 'GEN', 'is_mms_reserve' => 1),
                array('resource_id'=>'1AMBUK_U01', 'region' => 'LUZON' , 'type' => 'GEN', 'is_mms_reserve' => 1),
                array('resource_id'=>'1AMBUK_U02', 'region' => 'LUZON' , 'type' => 'GEN', 'is_mms_reserve' => 1),
                array('resource_id'=>'1AMBUK_U03', 'region' => 'LUZON' , 'type' => 'GEN', 'is_mms_reserve' => 1)
            )
        );
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
