<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeUniquePitcher extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stats', function (Blueprint $table) {
            $table->dropUnique(['player_id', 'year']);
            $table->unique(['player_id', 'year', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stats', function (Blueprint $table) {
            $table->dropUnique(['player_id', 'year', 'position']);
            $table->unique(['player_id', 'year']);
        });
    }
}
