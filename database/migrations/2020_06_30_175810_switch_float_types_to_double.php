<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SwitchFloatTypesToDouble extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stats', function (Blueprint $table) {
            $table->float('xwoba',8, 3)->nullable()->change();
            $table->float('secondhalf_xwoba', 8, 3)->nullable()->change();
            $table->float('opprpa', 8, 3)->nullable()->change();
            $table->float('oppops', 8, 3)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
