<?php

namespace App\Http\Controllers;

use App\Services\BinanceGetService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ChartController extends Controller
{
    public $binanceGetService;

    public function __construct(BinanceGetService $binanceGetService)
    {
        $this->binanceGetService = $binanceGetService;
    }

    public function data(Request $request): array
    {
//        if ($request->type === 'kucoin') {
//            return $this->kucoin($request);
//        }

        if ($request->type === 'binance') {
            return $this->binance($request);
        }

        return [];
    }

    public function binance(Request $request): array
    {
        $response1 = $this->binanceGetService->apiCall($request->s1, $request->candleType);
        $response2 = $this->binanceGetService->apiCall($request->s2, $request->candleType);

        $size_max = max(sizeof($response1), sizeof($response2) - 1);
        $size_min = min(sizeof($response1), sizeof($response2) - 1);

        $pair = [];
        for($i=0; $i<$size_max; $i++) {

            if ($i < $size_min) {

                $pair[] = [
                    $response1[$i][0], //timestamp
                    $response1[$i][1] / $response2[$i][1],
                    $response1[$i][2] / $response2[$i][2],
                    $response1[$i][3] / $response2[$i][3],
                    $response1[$i][4] / $response2[$i][4],
//                $response1[$i][5], // volume
                ];
            }
        }

        return [
            'first' => $this->formatBinanceResponse($response1),
            'pair' => array_reverse($pair),
            'second' => $this->formatBinanceResponse($response2),
            'events' => [
                'middlePrice1' => null,
                'middlePrice2' => null,
                'middlePrice3' => null,
            ]
        ];
    }

    public function formatBinanceResponse(array $data): array
    {
        $formatted = [];
        foreach($data as $item) {
            $formatted[] = [
                floatval($item[0]),
                floatval($item[1]),
                floatval($item[2]),
                floatval($item[3]),
                floatval($item[4]),
            ];
        }

        return array_reverse($formatted);
    }
    public function pair(): View
    {
        return view('pair');
    }
}
