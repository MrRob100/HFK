<?php

namespace App\Http\Controllers;

use App\Models\Result;

class HomeController extends Controller
{
    public function index()
    {
        $results = Result::where('sd_above', '<', 0.005)
            ->where('sd_below', '<', 0.005)
            ->where('count_above', '>', 30)
            ->orderBy('count_middle', 'DESC')
            ->limit('10')
            ->get();

        return view('home')->with('results', $results);
    }
}
