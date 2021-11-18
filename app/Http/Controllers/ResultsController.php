<?php

namespace App\Http\Controllers;

use App\Models\Result;
use Illuminate\Http\Request;

class ResultsController extends Controller
{
    public function index(Request $request)
    {
        return Result::where('sd_above', '<', 0.005)
            ->where('candle_type', $request->candle_type)
            ->where('sd_below', '<', 0.005)
            ->where('count_above', '>', 30)
            ->orderBy('count_middle', 'DESC')
            ->limit('10')
            ->get();
    }
}
