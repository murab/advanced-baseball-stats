<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Stat;

class TruController extends Controller
{
    public function index(Request $request, ?int $year = null)
    {
        if (empty($year)) { $year = date('Y'); }

        $stats = Stat::where('year', $year)->orderBy('tru', 'asc')->get();

        return view('tru', [
            'stats' => $stats,
        ]);
    }
}
