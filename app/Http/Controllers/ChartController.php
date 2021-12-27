<?php

namespace App\Http\Controllers;

use App\Services\BinanceGetService;
use App\Services\FormatPairService;
use App\Services\KucoinGetService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ChartController extends Controller
{
    public $binanceGetService;
    public $kucoinGetService;
    public $formatPairService;

    public function __construct(
        BinanceGetService $binanceGetService,
        FormatPairService $formatPairService,
        KucoinGetService $kucoinGetService
    )
    {
        $this->binanceGetService = $binanceGetService;
        $this->kucoinGetService = $kucoinGetService;
        $this->formatPairService = $formatPairService;
    }

    public function data(Request $request): array
    {
        if ($request->type === 'kucoin') {
            return $this->kucoin($request);
        }

        if ($request->type === 'binance') {
            return $this->binance($request);
        }

        return [];
    }

    public function binance(Request $request): array
    {
        $response1 = $this->binanceGetService->apiCall($request->s1, $request->candleType);
        $response2 = $this->binanceGetService->apiCall($request->s2, $request->candleType);

        $pair = $this->formatPairService->createBinancePairData($response1, $response2);

//        $size_max = max(sizeof($response1), sizeof($response2) - 1);
//        $size_min = min(sizeof($response1), sizeof($response2) - 1);
//
//        $pair = [];
//        for($i=0; $i<$size_max; $i++) {
//
//            if ($i < $size_min) {
//
//                $pair[] = [
//                    $response1[$i][0], //timestamp
//                    $response1[$i][1] / $response2[$i][1],
//                    $response1[$i][2] / $response2[$i][2],
//                    $response1[$i][3] / $response2[$i][3],
//                    $response1[$i][4] / $response2[$i][4],
////                $response1[$i][5], // volume
//                ];
//            }
//        }

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

    public function kucoin(Request $request): array
    {
        if ($request->s1 === 'RIF' && $request->s2 === 'BTC') {
            $rifbtc = $this->kucoinGetService->apiCall('RIF', $request->candleType, 'BTC');

            $response1 = $this->binanceGetService->apiCall('RIF', $request->candleType);
            $response2 = $this->kucoinGetService->apiCall('BTC', $request->candleType);

            return [
                'first' => $this->formatBinanceResponse($response1),
                'pair' => $this->formatKucoinResponse($rifbtc),
                'second' => $this->formatKucoinResponse($response2),
                'events' => [
                    'middlePrice1' => null,
                    'middlePrice2' => null,
                    'middlePrice3' => null,
                ]
            ];


        }

        $response1 = $this->kucoinGetService->apiCall($request->s1, $request->candleType);
        $response2 = $this->kucoinGetService->apiCall($request->s2, $request->candleType);

        $pair = $this->formatPairService->createKucoinPairData($response1, $response2);

        return [
            'first' => $this->formatKucoinResponse($response1),
            'pair' => array_reverse($pair),
            'second' => $this->formatKucoinResponse($response2),
            'events' => [
                'middlePrice1' => null,
                'middlePrice2' => null,
                'middlePrice3' => null,
            ]
        ];
    }

    public function formatKucoinResponse(array $data): array
    {
        $formatted = [];
        foreach($data as $item) {
            $formatted[] = [
                floatval($item[0]) * 1000,
                floatval($item[1]),
                floatval($item[3]),
                floatval($item[4]),
                floatval($item[2]),
            ];
        }

        return array_reverse($formatted);
    }

    public function pair(): View
    {
        return view('pair');
    }
}
