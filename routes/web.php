<?php

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

Route::get('/', function () {
    return redirect('ranks');
});

Route::get('/ranks', function(Request $request) {
    $year = date('Y');
    $position = 'sp';
    if ($request->get('year')) { $year = $request->get('year'); }
    if ($request->get('position')) { $position = $request->get('position'); }
    return redirect()->route('ranks', ['year' => $year, 'position' => $position]);
});

Route::get('/ranks/{year?}/{position?}', 'TruController@index')->where([
    'year' => '2[0-9]{3}',
    'position' => 'sp|rp',
])->name('ranks');
