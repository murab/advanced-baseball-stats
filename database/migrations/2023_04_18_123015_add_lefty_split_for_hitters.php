<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLeftySplitForHitters extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hitters', function (Blueprint $table) {
            $table->float('vsleft_wrc_plus')->nullable();
            $table->float('secondhalf_vsleft_wrc_plus')->nullable();
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
            $table->dropColumn(['vsleft_wrc_plus', 'secondhalf_vsleft_wrc_plus']);
        });
    }
}
