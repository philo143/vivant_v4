<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAspaNominationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('aspa_nominations', function (Blueprint $table) {
            $table->decimal('available_capacity',20,10)->nullable()->after('resource_id');
            $table->decimal('pump',20,10)->nullable()->after('available_capacity');
            $table->decimal('rr',20,10)->nullable()->after('pump');
            $table->decimal('cr',20,10)->nullable()->after('rr');
            $table->decimal('dr',20,10)->nullable()->after('cr');
            $table->decimal('rps',20,10)->nullable()->after('dr');
            $table->decimal('nominated_price',20,10)->nullable()->after('rps');
            $table->string('filename',100)->nullable()->after('submitted_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('plants', function (Blueprint $table) {
            $table->dropColumn('available_capacity');
            $table->dropColumn('pump');
            $table->dropColumn('rr');
            $table->dropColumn('cr');
            $table->dropColumn('dr');
            $table->dropColumn('rps');
            $table->dropColumn('nominated_price');
            $table->dropColumn('filename');
        });
    }
}
