<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InitialSchema extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('players', function (Blueprint $table) {
            $table->id('id');
            $table->string('name')->unique();
            $table->integer('fg_id')->nullable();
            $table->integer('bs_id')->nullable();
            $table->integer('bp_id')->nullable();

            $table->date('created_at');
            $table->date('updated_at');
        });

        Schema::create('stats', function (Blueprint $table) {
            $table->id('id');
            $table->bigInteger('player_id');

            $table->integer('year');

            $table->integer('age')->nullable();

            $table->integer('g')->nullable();
            $table->integer('secondhalf_g')->nullable();

            $table->integer('gs')->nullable();
            $table->integer('secondhalf_gs')->nullable();

            $table->float('ip')->nullable();
            $table->float('secondhalf_ip')->nullable();

            $table->integer('pa')->nullable();
            $table->integer('secondhalf_pa')->nullable();

            $table->integer('k')->nullable();
            $table->integer('secondhalf_k')->nullable();

            $table->float('k_per_game')->nullable();
            $table->float('secondhalf_k_per_game')->nullable();

            $table->float('swstr_percentage')->nullable();
            $table->float('secondhalf_swstr_percentage')->nullable();

            $table->float('xwoba')->nullable();
            $table->float('secondhalf_xwoba')->nullable();

            $table->float('k_percentage_plus')->nullable();
            $table->float('secondhalf_k_percentage_plus')->nullable();

            $table->float('k_percentage')->nullable();
            $table->float('secondhalf_k_percentage')->nullable();

            $table->float('bb_percentage')->nullable();
            $table->float('secondhalf_bb_percentage')->nullable();

            $table->float('tru')->nullable();
            $table->float('secondhalf_tru')->nullable();

            $table->float('velo')->nullable();
            $table->float('secondhalf_velo')->nullable();

            $table->float('opprpa')->nullable();
            $table->float('oppops')->nullable();

            $table->date('created_at');
            $table->date('updated_at');

            $table->foreign('player_id')->references('id')->on('players');
            $table->unique(['player_id', 'year']);

            /**
             *                     'k_percentage' => $player_data['k_percentage'],
            'bb_percentage' => $player_data['bb_percentage'],
            'kbb_percentage' => $player_data['kbb_percentage'],
            'swstr_percentage' => $player_data['swstr_percentage'],
            'k_percentage_plus' => $player_data['k_percentage_plus'],
            'age' => $player_data['age'],
            'g' => $player_data['g'],
            'k' => $player_data['k'],
            'k_per_game' => $player_data['k'] / $player_data['g'],
            'gs' => $player_data['gs'],
            'ip' => $player_data['ip'],
            'velo' => $player_data['velo']
             */
        });

        Schema::create('leagues', function (Blueprint $table) {
            $table->id('id');
            $table->date('created_at');
            $table->date('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('stats');
        Schema::drop('players');
        Schema::drop('leagues');
    }
}
