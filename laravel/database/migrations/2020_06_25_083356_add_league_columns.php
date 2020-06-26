<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLeagueColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('leagues', function (Blueprint $table) {
            $table->integer('year');
            $table->float('velo')->nullable();
            $table->float('swstr_percentage')->nullable();
            $table->float('kbb_percentage')->nullable();
            $table->float('era')->nullable();
            $table->float('whip')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leagues', function (Blueprint $table) {
            $table->dropColumn(['year', 'velo', 'swstr_percentage', 'kbb_percentage', 'era', 'whip']);
        });
    }
}
