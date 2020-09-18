<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBbPercentageToHitter extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hitters', function (Blueprint $table) {
            $table->float('bb_percentage',8, 3)->nullable();
            $table->float('secondhalf_bb_percentage', 8, 3)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hitters', function (Blueprint $table) {
            $table->dropColumn('bb_percentage');
            $table->dropColumn('secondhalf_bb_percentage');
        });
    }
}
