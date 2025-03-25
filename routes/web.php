<?php

use App\Player;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'HomepageController@index')->name('homepage');

Route::get('/ranks', function(Request $request) {
    $year = date('Y');
    $position = 'sp';
    if ($request->get('year')) { $year = $request->get('year'); }
    if ($request->get('position')) { $position = $request->get('position'); }
    return redirect()->route('ranks', ['year' => $year, 'position' => $position]);
});

Route::get('/pitchers/{year?}/{position?}', 'PitcherController@index')->where([
    'year' => '2[0-9]{3}',
    'position' => 'sp|rp',
])->name('pitcher_ranks');

Route::get('/pitcher/{slug}', 'PitcherController@individual')->name('pitcher');

Route::get('/hitters/{year?}', 'HitterController@index')->where([
    'year' => '2[0-9]{3}',
])->name('hitter_ranks');
Route::get('/hitter/{slug}', 'HitterController@individual')->name('hitter');

Route::get('/articles', 'ArticleController@index')->name('articles');

Route::get('/about', 'ArticleController@about')->name('about');

Route::get('/articles/{slug}', 'ArticleController@showPost')->name('article');

Route::get('/api/hitters/{year}/{pa_min}/{pa_per_g_min}/{sb_min}', 'HitterController@filter');
Route::get('/api/pitchers/{year}/{position}/{min_ip}', 'PitcherController@filter');

Route::post('/gotoplayer', function(Request $request) {
    $player = $request->post('gotoplayer');
    $slug = str_replace(' ', '-', strtolower($player));
    Player::where('slug', $slug)->first();
    if ($player = Player::where('slug', $slug)->first()) {
        $hitter = \App\Hitter::where('player_id', $player->id)->get();
        $pitcher = \App\Stat::where('player_id', $player->id)->get();
        if (count($pitcher)) {
            return redirect()->route('pitcher', ['slug' => $slug]);
        } else if (count($hitter)) {
            return redirect()->route('hitter', ['slug' => $slug]);
        }
    }
    abort(404);
})->name('gotoplayer');

Route::get('/{any}', function ($any) {
    $player = $any;
    $slug = str_replace(' ', '-', strtolower($player));
    $player = Player::where('slug', 'ilike', $slug.'%')->first();
    if (!$player) {
        $player = Player::where('slug2', 'ilike', $slug.'%')->first();
    }
    if ($player) {
        $slug = $player->slug;
        $hitter = \App\Hitter::where('player_id', $player->id)->get();
        $pitcher = \App\Stat::where('player_id', $player->id)->get();

        $total_pa = 0;
        $total_ip = 0;

        foreach ($hitter as $stat) {
            $total_pa += $stat->pa;
        }
        foreach ($pitcher as $stat) {
            $total_ip += $stat->ip;
        }

        if ($total_ip * 3 > $total_pa) {
            return redirect()->route('pitcher', ['slug' => $slug]);
        } else if (count($hitter)) {
            return redirect()->route('hitter', ['slug' => $slug]);
        }
    }
    abort(404);
});
