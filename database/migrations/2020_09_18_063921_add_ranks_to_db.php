<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRanksToDb extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stats', function (Blueprint $table) {
            $table->integer('k_rank')->nullable();
            $table->integer('secondhalf_k_rank')->nullable();
            $table->integer('ip_per_g_rank')->nullable();
            $table->integer('secondhalf_ip_per_g_rank')->nullable();
            $table->integer('xwoba_rank')->nullable();
            $table->integer('secondhalf_xwoba_rank')->nullable();
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
