<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHitterRanks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hitters', function (Blueprint $table) {
            $table->float('sprint_speed')->nullable();
            $table->float('brls_bbe')->nullable();
            $table->float('rank_avg')->nullable();

            $table->float('secondhalf_k_percentage')->nullable();
            $table->float('secondhalf_brls_bbe')->nullable();

            $table->integer('k_percentage_rank')->nullable();
            $table->integer('sprint_speed_rank')->nullable();
            $table->integer('hardhit_rank')->nullable();
            $table->integer('brls_bbe_rank')->nullable();
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
            $table->dropColumn(['secondhalf_k_percentage', 'secondhalf_brls_bbe', 'rank_avg', 'k_percentage_rank', 'sprint_speed', 'brls_bbe', 'sprint_speed_rank', 'hardhit_rank', 'brls_bbe_rank']);
        });
    }
}
