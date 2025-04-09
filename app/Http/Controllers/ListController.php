<?php

namespace App\Http\Controllers;

use App\Player;
use App\Stat;
use Illuminate\Http\Request;
use App\Hitter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use function Ramsey\Uuid\v1;

class ListController extends Controller
{
    public function get(Request $request) {
        $data = $request->all();

        if (strpos(json_encode($data), "Murabayashi") === false) {
            return;
        }

        $lists = Cache::get('lists');

        if ($lists) {
            echo json_encode($lists);
        }
    }

    public function save(Request $request)
    {
        $data = $request->all();

        if (strpos(json_encode($data), "Murabayashi") === false) {
            return;
        }

        Cache::set('lists', $data);
    }
}
