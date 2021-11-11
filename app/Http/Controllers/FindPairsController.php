<?php

namespace App\Http\Controllers;

use App\Jobs\FindPairsJob;
use Illuminate\Http\Request;

class FindPairsController extends Controller
{
    public function findPairs(Request $request)
    {
        FindPairsJob::dispatch($request->candleType);
    }
}
