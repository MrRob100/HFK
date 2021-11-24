<?php

namespace App\Http\Controllers;

use App\Models\Result;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResultsController extends Controller
{
    public function index(Request $request)
    {
        //where up neigbours and down neighbours are similar, but need to get value using SQL do it

        if ($request->band) {
            $result = Result::where('candle_type', $request->candleType)
                ->orderBy($request->band, 'DESC')
                ->where(str_replace('up', '', $request->band . 'down'), '>', 5)
                ->where(DB::raw('convert(upneighbours - downneighbours, UNSIGNED)'), '<', 10)
                ->paginate(20);
        } else {
            $result = Result::where('candle_type', $request->candleType)->paginate(10);
        }

        return $result;
    }
}
