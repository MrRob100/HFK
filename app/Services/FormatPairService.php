<?php

namespace App\Services;

use App\Models\Truth;
use Carbon\Carbon;

class FormatPairService
{
    public function createPairData($data1, $data2): array
    {
        $size_max = max(sizeof($data1), sizeof($data2) - 1);
        $size_min = min(sizeof($data1), sizeof($data2) - 1);

        $pair = [];
        for($i=0; $i<$size_max; $i++) {

            if ($i < $size_min && isset($data1[$i])) {

                $unix = $data1[$i][0];
                $o = $data1[$i][1] / $data2[$i][1];
                $h = $data1[$i][2] / $data2[$i][2];
                $l = $data1[$i][3] / $data2[$i][3];
                $c = $data1[$i][4] / $data2[$i][4];

                $pair[] = [
                    $unix,
                    $o,
                    $h,
                    $l,
                    $c,
                ];
            }
        }

        return $pair;
    }

    public function getCandlesData($symbol, $candleType, $exchange): array
    {
        $dir = public_path() . '/data';
        $file = $dir . '/' . $symbol . '.json';

        if ($candleType === "1h") {

            $candleTypeKucoin = "1hour";

            $startTime = Carbon::now()->subMonth()->unix();
        } else {

            $candleTypeKucoin = "1day";

            $startTime = Carbon::now()->subMonths(4)->unix(); //could make this deeper easily
        }

        if (file_exists($file)) {
            $candles = json_decode(file_get_contents($file));
        } else {
            $candles = [];
            if ($exchange === 'binance') {

                $start = $startTime * 1000;
                $candles = json_decode(file_get_contents("https://www.binance.com/api/v3/klines?symbol={$symbol}&interval={$candleType}&startTime=$start"), true);
            }

            if ($exchange === 'kucoin') {
                $candles = json_decode(file_get_contents("https://api.kucoin.com/api/v1/market/candles?type={$candleTypeKucoin}&symbol={$symbol}&startAt={$startTime}"), true)['data'];
            }
            file_put_contents($file, json_encode($candles));
        }

        return $candles;
    }
}
