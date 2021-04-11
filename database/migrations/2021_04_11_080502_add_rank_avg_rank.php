<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRankAvgRank extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hitters', function (Blueprint $table) {
            $table->integer('rank_avg_rank')->nullable();
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
            $table->dropColumn(['rank_avg_rank']);
        });
    }
}
