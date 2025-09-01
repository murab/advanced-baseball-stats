<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSecondhalfHitterRankCols extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hitters', function (Blueprint $table) {
            $table->integer('secondhalf_hr_per_g_rank')->nullable();
            $table->integer('secondhalf_avg_rank')->nullable();
            $table->float('secondhalf_hr_per_g')->nullable();
            $table->float('secondhalf_pa_per_g')->nullable();
            $table->integer('secondhalf_xba_rank')->nullable();
            $table->float('secondhalf_xba',8,3)->nullable();
            $table->integer('secondhalf_xwoba_rank')->nullable();
            $table->float('secondhalf_rank_avg')->nullable();
            $table->integer('secondhalf_rank_avg_rank')->nullable();
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
            $table->dropColumn([
                'secondhalf_hr_per_g_rank',
                'secondhalf_avg_rank',
                'secondhalf_hr_per_g',
                'secondhalf_pa_per_g',
                'secondhalf_xba_rank',
                'secondhalf_xba',
                'secondhalf_xwoba_rank',
                'secondhalf_rank_avg_rank',
                'secondhalf_rank_avg',
            ]);
        });
    }
}
