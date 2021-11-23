<?php

namespace App\Http\Controllers;

use App\Jobs\FindPairsJob;
use App\Services\FormatPairService;
use Illuminate\Http\Request;

class FindPairsController extends Controller
{
    protected $formatPairsService;

    public function findPairs(Request $request, FormatPairService $formatPairService)
    {
        $this->formatPairsService = $formatPairService;

        FindPairsJob::dispatch($request->candleType, $formatPairService);
    }
}
