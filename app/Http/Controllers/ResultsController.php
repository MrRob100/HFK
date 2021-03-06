<?php

namespace App\Http\Controllers;

use App\Models\Hour;
use App\Models\KucoinResult;
use App\Models\Result;
use Illuminate\Http\Request;

class ResultsController extends Controller
{
    public function index(Request $request)
    {
        if ($request->marketType === 'binance') {
            if ($request->candleType === '1h') {
                return Hour::orderBy('usn_25_50')->paginate(20);
            }

            if ($request->frozen) {
                return Result::where('symbol1', $request->frozen)
                    ->orWhere('symbol2', $request->frozen)
                    ->orderBy('usn_25_50')
                    ->paginate(20);
            }

            return Result::where('candle_type', $request->candleType)->orderBy('usn_25_50')->paginate(20);
        } elseif ($request->marketType === 'kucoin') {

            if ($request->frozen) {
                return KucoinResult::where('symbol1', $request->frozen)
                    ->orWhere('symbol2', $request->frozen)
                    ->orderBy('usn_25_50')
                    ->paginate(20);
            }

            return KucoinResult::orderBy('usn_25_50')->paginate(20);
        } else {
            dd('no exchange type');
        }
    }
}
