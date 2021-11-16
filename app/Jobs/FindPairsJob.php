<?php

namespace App\Jobs;

use App\Models\Message;
use App\Models\Result;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class FindPairsJob
//class FindPairsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $candleType;

    protected $candleKey = [
        'hour' => '1h',
        'day' => '1d',
    ];

    public function __construct(string $candleType)
    {
        $this->candleType = $this->candleKey[$candleType];
    }

    public function handle()
    {
        if (Cache::has('all')) {
            $data = Cache::get('all');
        } else {
            $data = collect(json_decode(file_get_contents('https://api3.binance.com/api/v3/ticker/24hr')));

            Cache::put('all', $data, 600);
        }

        $filtered = $data->filter(function ($value, $key) {

            //better way of slimming down results to test

            if (
                !strpos($value->symbol, 'DOWN') !== false
                && !strpos($value->symbol, 'BULL') !== false
                && !strpos($value->symbol, 'BEAR') !== false
                && strpos($value->symbol, 'USDT') !== false
                && strpos($value->symbol, 'O') !== false //to slim down while testing
                && strpos($value->symbol, 'S') !== false //to slim down while testing
            ) {
                return $value;
            }

//            return strpos($value->symbol, 'USDT') !== false ? $value : null;
//            return strpos($value->symbol, 'SUSDT') !== false ? $value : null;
        });

        $checked = 0;
//        foreach ($filtered->toArray() as $symbolOuter) {
//
//            $dataOuter = $this->getCandlesData($symbolOuter->symbol);
//
//            foreach ($filtered->toArray() as $symbolInner) {
//
//                if ($symbolOuter->symbol !== $symbolInner->symbol) {
//                    $dataInner = $this->getCandlesData($symbolInner->symbol);
//
//                    $pairData = $this->createPairData($dataOuter, $dataInner);
//
//                    if (sizeof($pairData) > 90) {
//                        $results = $this->performCalcs($pairData, $this->candleType, $symbolInner->symbol, $symbolOuter->symbol);
//                    }
//
//                    $total = $filtered->count() * $filtered->count();
//
//                    $checked++;
//                    if ($checked % 100 === 0) {
//
//                        Message::updateOrCreate(
//                            [
//                                'type' => 'pair_check',
//                            ],
//                            [
//                                'message' => "checked $checked out of $total ($symbolOuter->symbol X $symbolInner->symbol)",
//                                'type' => 'pair_check',
//                            ]
//                        );
//                    }
//
//                    if ($checked === $total - 1) {
//                        Message::updateOrCreate(
//                            [
//                                'type' => 'pair_check',
//                            ],
//                            [
//                                'message' => "checked all $total pairs",
//                                'type' => 'pair_check',
//                            ]
//                        );
//                    }
//                }
//            }
//        }

        //get akronuls
        $akro = $this->getCandlesData('AKROUSDT');
        $nuls = $this->getCandlesData('NULSUSDT');

        $akronuls = $this->createPairData($akro, $nuls);

        $akronuls_result = $this->performCalcs($akronuls, $this->candleType, 'AKROUSDT', 'NULSUSDT');
    }

    public function getCandlesData($symbol): array
    {
        //create file if doesnt exist
        $fileName = public_path() . '/data/' . $symbol . '.json';
        if (file_exists($fileName)) {
            $candles = file_get_contents($fileName);
        } else {
            $startTime = 1627776000000;
            $endTime = 1635724799000;

            $candles = file_get_contents("https://www.binance.com/api/v3/klines?symbol={$symbol}&interval={$this->candleType}&startTime=$startTime&endTime=$endTime");
            file_put_contents($fileName, $candles);
        }

        return json_decode($candles, true);
    }

    public function performCalcs($pairData, $candleType, $symbol1, $symbol2)
    {
        $maPoint = 10;

        $candleAves = [];
        $pureAves = [];
        $totalAves = 0;
        foreach ($pairData as $item) {
            $collection = collect($item);

            $first_key = $collection->keys()->first();

            $candleAve = ($item[1] + $item[4]) / 2;
            $candleAves[] = [
                'time' => $item[0],
                'o' => $item[1],
                'h' => $item[2],
                'l' => $item[3],
                'c' => $item[4],
                'candleAve' => $candleAve,
            ];

            array_push($pureAves, $candleAve);
            $totalAves += $candleAve;
        }

        $ave = $totalAves / sizeof($pairData);

        $thresh = 0.05;

        $countAbove = 0;
        $countBelow = 0;
        $uppers = [];
        $lowers = [];
        foreach ($pureAves as $pureAve) {
            if ($pureAve > $ave) {
                //above
                $threshUpper = $ave + ($ave * $thresh);
                if ($pureAve > $threshUpper) {
                    $countAbove ++;
                    $uppers[] = $pureAve;
                }
            } else {
                //below
                $threshLower = $ave - ($ave * $thresh);
                if ($pureAve < $threshLower) {
                    $countBelow ++;
                    $lowers[] = $pureAve;
                }
            }
        }

//        $pureAves;

//        $sd = stats_standard_deviation(collect($candleAves)->pluck('candleAve'));

        $sdAbove = $this->standardDeviation($uppers);
        $sdBelow = $this->standardDeviation($lowers);

        Result::create([
            'pair' => "$symbol1$symbol2",
            'candle_type' => $candleType,
            'count_above' => $countAbove,
            'count_below' => $countBelow,
            'sd_above' => $sdAbove,
            'sd_below' => $sdBelow,
            'sd_ab' => $sdAbove * $sdBelow,
        ]);

        return [];
    }

    public function createPairData($data1, $data2): array
    {
        $size_max = max(sizeof($data1), sizeof($data2) - 1);
        $size_min = min(sizeof($data1), sizeof($data2) - 1);

        $pair = [];
        for($i=0; $i<$size_max; $i++) {

            if ($i < $size_min) {

                $pair[] = [
                    $data1[$i][0], //timestamp
                    $data1[$i][1] / $data2[$i][1],
                    $data1[$i][2] / $data2[$i][2],
                    $data1[$i][3] / $data2[$i][3],
                    $data1[$i][4] / $data2[$i][4],
//                $response1[$i][5], // volume
                ];
            }
        }

        return $pair;
    }

    public function standardDeviation($arr)
    {
        $num_of_elements = count($arr);

        if ($num_of_elements > 0) {
            $variance = 0.0;

            // calculating mean using array_sum() method
            $average = array_sum($arr)/$num_of_elements;

            foreach($arr as $i)
            {
                // sum of squares of differences between
                // all numbers and means.
                $variance += pow(($i - $average), 2);
            }

            return (float)sqrt($variance/$num_of_elements);
        } else {
            return null;
        }
    }
}
