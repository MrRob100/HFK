<?php

namespace App\Http\Controllers;

use App\Models\Result;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResultsController extends Controller
{
    public function index(Request $request)
    {
        //where request band is bigger than other bands
        //some kind of straightness score? (in job)
        //where corresponding down band is bigger than other bands

        if ($request->band) {

            $corresp_down = str_replace('up', '', $request->band . 'down');

            $query = Result::where('candle_type', $request->candleType)
                ->orderBy($request->band, 'DESC')
                ->where($corresp_down, '>', 5)
                ->where(DB::raw('convert(upneighbours - downneighbours, UNSIGNED)'), '<', 10);

            if ($request->band !== 'threeup') {
                $query = $query->whereRaw($request->band . ' > threeup');
                $query = $query->whereRaw($corresp_down . ' > threedown');
            }

            if ($request->band !== 'fourup') {
                $query = $query->whereRaw($request->band . ' > fourup');
                $query = $query->whereRaw($corresp_down . ' > fourdown');
            }

            if ($request->band !== 'fiveup') {
                $query = $query->whereRaw($request->band . ' > fiveup');
                $query = $query->whereRaw($corresp_down . ' > fivedown');
            }

            if ($request->band !== 'sixup') {
                $query = $query->whereRaw($request->band . ' > sixup');
                $query = $query->whereRaw($corresp_down . ' > sixdown');
            }

            $result = $query->paginate(20);
        } else {
            $result = Result::where('candle_type', $request->candleType)->paginate(10);
        }

        return $result;
    }
}
