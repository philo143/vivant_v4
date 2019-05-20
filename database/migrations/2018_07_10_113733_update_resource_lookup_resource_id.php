<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateResourceLookupResourceId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement(
            "UPDATE resource_lookup SET resource_id = if(resource_id REGEXP '^[1-9][A-Za-z]', CONCAT('0',resource_id),resource_id)"
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement(
            "UPDATE resource_lookup SET resource_id = if(resource_id REGEXP '^[0][1-9][A-Za-z]', SUBSTR(resource_id,2),resource_id)"
        );
    }
}
