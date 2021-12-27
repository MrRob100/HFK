<?php

namespace App\Services;

use Carbon\Carbon;

class FormatPairService
{
    public function createBinancePairData($data1, $data2): array
    {
        $size_max = max(sizeof($data1), sizeof($data2) - 1);
        $size_min = min(sizeof($data1), sizeof($data2) - 1);

        $pair = [];
        for($i=0; $i<$size_max; $i++) {

            if ($i < $size_min && isset($data1[$i])) {

                $unix = intval($data1[$i][0]);
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

    public function createKucoinPairData($data1, $data2): array
    {
        $size_max = max(sizeof($data1), sizeof($data2) - 1);
        $size_min = min(sizeof($data1), sizeof($data2) - 1);

        $pair = [];
        $i_data1 = 0;
        $i_data2 = 0;
        for($i=0; $i<$size_max; $i++) {

            if ($i < $size_min && isset($data1[$i])) {

                if (isset($data1[$i_data1]) && isset($data2[$i_data2])) {
                    if ($data1[$i_data1][0] > $data2[$i_data2][0]) {
                        $i_data2++;
                    }

                    if (isset($data1[$i_data1]) && isset($data2[$i_data2])) {
                        if ($data1[$i_data1][0] < $data2[$i_data2][0]) {
                            $i_data1++;
                        }

                        if (isset($data1[$i_data1]) && isset($data2[$i_data2]) && $data1[$i_data1][0] === $data2[$i_data2][0]) {

                            $unix = intval($data1[$i_data1][0]) * 1000;
                            $o = $data1[$i_data1][1] / $data2[$i_data2][1];
                            $h = $data1[$i_data1][3] / $data2[$i_data2][3];
                            $l = $data1[$i_data1][4] / $data2[$i_data2][4];
                            $c = $data1[$i_data1][2] / $data2[$i_data2][2];

                            $pair[] = [
                                $unix,
                                $o,
                                $h,
                                $l,
                                $c,
                            ];
                        }
                    }
                }
            }
            $i_data1++;
            $i_data2++;
        }

        return $pair;
    }

    public function getCandlesData($symbol, $candleType, $exchange): array
    {
        $dir = public_path() . '/data';
        $file = $dir . '/' . str_replace('-', '', $symbol) . '.json';

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
                try {
                    $candles = json_decode(file_get_contents("https://www.binance.com/api/v3/klines?symbol={$symbol}&interval={$candleType}&startTime=$start"), true);
                }
                catch(\Exception $e) {
                    dump($e->getMessage());
                    sleep(10);
                }
            }

            if ($exchange === 'kucoin') {
                try {
                    $candles = json_decode(file_get_contents("https://api.kucoin.com/api/v1/market/candles?type={$candleTypeKucoin}&symbol={$symbol}&startAt={$startTime}"), true)['data'];

                    $candlesFormatted = [];
                    foreach ($candles as $candle) {
                        $candlesFormatted[] = [
                            intval($candle[0] * 1000),
                            $candle[1],
                            $candle[2],
                            $candle[3],
                            $candle[4],
                        ];
                    }

                    file_put_contents($file, json_encode($candlesFormatted));
                }

                catch(\Exception $e) {
                    dump($e->getMessage());
                    sleep(40);
                }
            }
        }

        return $candles;
    }
}
