<?php

namespace App\Http\Controllers;

use App\Models\Result;
use Illuminate\Http\Request;

class ResultsController extends Controller
{
    public function index(Request $request)
    {
        if ($request->band) {
            $result = Result::where('candle_type', $request->candleType)
                ->orderBy($request->band, 'DESC')
                ->where(str_replace('up', '', $request->band . 'down'), '>', 5)
                ->paginate(10);
        } else {
            $result = Result::where('candle_type', $request->candleType)->paginate(10);
        }

        return $result;
    }
}
