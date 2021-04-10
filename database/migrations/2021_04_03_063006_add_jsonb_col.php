<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddJsonbCol extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stats', function (Blueprint $table) {
            $table->jsonb('json')->nullable();
        });
        Schema::table('stats', function (Blueprint $table) {
            $table->jsonb('secondhalf_json')->nullable();
        });
        Schema::table('hitters', function (Blueprint $table) {
            $table->jsonb('json')->nullable();
        });
        Schema::table('hitters', function (Blueprint $table) {
            $table->jsonb('secondhalf_json')->nullable();
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
            $table->dropColumn(['json', 'secondhalf_json']);
        });
        Schema::table('stats', function (Blueprint $table) {
            $table->dropColumn(['json', 'secondhalf_json']);
        });
    }
}
