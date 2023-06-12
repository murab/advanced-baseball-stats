<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPullsFlyballs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hitters', function (Blueprint $table) {
            $table->integer('flyballs')->nullable();
            $table->integer('pulled_flyballs')->nullable();
            $table->float('pulled_flyball_percentage')->nullable();
            $table->float('pulled_flyballs_per_g')->nullable();
            $table->integer('pulled_flyballs_per_g_rank')->nullable();
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
            $table->dropColumn(['flyballs', 'pulled_flyballs', 'pulled_flyball_percentage', 'pulled_flyballs_per_g', 'pulled_flyballs_per_g_rank']);
        });
    }
}
