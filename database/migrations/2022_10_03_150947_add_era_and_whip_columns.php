<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEraAndWhipColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stats', function (Blueprint $table) {
            $table->float('era', 8, 2)->nullable();
            $table->float('whip', 8, 2)->nullable();
            $table->float('secondhalf_era', 8, 2)->nullable();
            $table->float('secondhalf_whip', 8, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stats', function (Blueprint $table) {
            $table->dropColumn(['era', 'whip', 'secondhalf_era', 'secondhalf_whip']);
        });
    }
}
