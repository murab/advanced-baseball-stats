<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class HittersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hitters', function (Blueprint $table) {
            $table->id('id');
            $table->unsignedBigInteger('player_id');

            $table->integer('year');

            $table->integer('age')->nullable();

            $table->integer('g')->nullable();
            $table->integer('secondhalf_g')->nullable();

            $table->integer('pa')->nullable();
            $table->integer('secondhalf_pa')->nullable();

            $table->integer('r')->nullable();
            $table->integer('secondhalf_r')->nullable();

            $table->float('avg',8, 3)->nullable();
            $table->integer('secondhalf_avg')->nullable();

            $table->integer('hr')->nullable();
            $table->integer('secondhalf_hr')->nullable();

            $table->integer('rbi')->nullable();
            $table->integer('secondhalf_rbi')->nullable();

            $table->integer('sb')->nullable();
            $table->integer('secondhalf_sb')->nullable();

            $table->float('k_percentage',8, 3)->nullable();
            $table->float('secondhalf_k_percentage', 8, 3)->nullable();

            $table->float('swstr_percentage', 8, 3)->nullable();
            $table->float('secondhalf_swstr_percentage', 8, 3)->nullable();

            $table->float('xslg', 8, 3)->nullable();
            $table->float('secondhalf_xslg', 8, 3)->nullable();

            $table->float('xwoba', 8, 3)->nullable();
            $table->float('secondhalf_xwoba', 8, 3)->nullable();

            $table->float('hardhit_percentage', 8, 3)->nullable();
            $table->float('secondhalf_hardhit_percentage', 8, 3)->nullable();

            $table->integer('wrc_plus')->nullable();
            $table->integer('secondhalf_wrc_plus')->nullable();

            $table->date('created_at');
            $table->date('updated_at');

            $table->unique(['player_id', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('hitters');
    }
}
