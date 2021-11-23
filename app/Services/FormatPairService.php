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

            if ($i < $size_min) {

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

    public function getCandlesData($symbol, $candleType): array
    {
        $today = Carbon::now()->startOfDay()->unix();

        $dirToday = public_path() . '/data/' . $today;

        $dir = $dirToday . '/' . $candleType;

        if (!is_dir($dirToday)) {
            mkdir($dirToday);

            if (!is_dir($dir)) {
                mkdir($dir);
            }
        }

        $this->deleteYesterday($candleType);

        //create file if doesnt exist
        $fileName = $dir . '/' . $symbol . '.json';
        if (file_exists($fileName)) {
            $candles = file_get_contents($fileName);
        } else {
            if ($candleType === "1h") {
                $startTime = Carbon::now()->subMonth()->unix() * 1000;
            } else {
                //should be 1d
                $startTime = Carbon::now()->subMonths(4)->unix() * 1000;
            }

            $candles = file_get_contents("https://www.binance.com/api/v3/klines?symbol={$symbol}&interval={$candleType}&startTime=$startTime");
            file_put_contents($fileName, $candles);
        }

        return json_decode($candles, true);
    }

    public function deleteYesterday($candleType): void
    {
        $day = Carbon::yesterday()->unix();

        $path = public_path() . '/data/' . $day . '/' . $candleType;

        if (is_dir($path)) {
            $files = glob($path . '/*');
            foreach ($files as $file) {
                is_dir($file) ? removeDirectory($file) : unlink($file);
            }
            rmdir($path);
        }
    }
}
