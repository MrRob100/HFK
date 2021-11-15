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
                && strpos($value->symbol, 'USDT') !== false
                && strpos($value->symbol, 'S') !== false //to slim down
            ) {
                return $value;
            }

//            return strpos($value->symbol, 'USDT') !== false ? $value : null;
//            return strpos($value->symbol, 'SUSDT') !== false ? $value : null;
        });

        $checked = 0;
        foreach ($filtered->toArray() as $symbolOuter) {

            $dataOuter = $this->getCandlesData($symbolOuter->symbol);

            foreach ($filtered->toArray() as $symbolInner) {

                if ($symbolOuter->symbol !== $symbolInner->symbol) {
                    $dataInner = $this->getCandlesData($symbolInner->symbol);

                    $pairData = $this->createPairData($dataOuter, $dataInner);

                    dump($symbolInner->symbol);
                    dump($symbolOuter->symbol);

                    $results = $this->performCalcs($pairData, $symbolInner->symbol, $symbolOuter->symbol);

                    $total = $filtered->count() * $filtered->count();

                    $checked++;
                    if ($checked % 100 === 0) {

                        Message::updateOrCreate(
                            [
                                'type' => 'pair_check',
                            ],
                            [
                                'message' => "checked $checked out of $total ($symbolOuter->symbol X $symbolInner->symbol)",
                                'type' => 'pair_check',
                            ]
                        );
                    }

                    if ($checked === $total - 1) {
                        Message::updateOrCreate(
                            [
                                'type' => 'pair_check',
                            ],
                            [
                                'message' => "checked all $total pairs",
                                'type' => 'pair_check',
                            ]
                        );
                    }
                }
            }
        }
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

    public function performCalcs($pairData, $symbol1, $symbol2): array
    {
        $maPoint = 10;

        $candleAves = [];
        foreach ($pairData as $item) {
            $collection = collect($item);

            $first_key = $collection->keys()->first();
            $notime = $collection->forget($first_key);

            $candleAve = $notime->sum() / 4;
            $candleAves[] = [
                'time' => $item[0],
                'o' => $item[1],
                'h' => $item[2],
                'l' => $item[3],
                'c' => $item[4],
                'candleAve' => $candleAve,
            ];
        }

        $i = 0;
        $totalTilTen = 0;
        $ma = [];
        $crossings = 0;
        $totalDistanceFromMa = 0;
        foreach ($candleAves as $candleAve) {
            $totalTilTen += $candleAve['candleAve'];

            if ($i >= $maPoint) {
                $totalTilTen -= $candleAves[$i - $maPoint]['candleAve'];
            }

            if ($i === 0) {
                $maDiv = 1;
            } elseif ($i < 10) {
                $maDiv = $i + 1;
            } else {
                $maDiv = 10;
            }

            $ma = $totalTilTen / $maDiv;

            $ma[] = [
                'time' => $candleAve['time'],
                'o' => $candleAve['o'],
                'h' => $candleAve['h'],
                'l' => $candleAve['l'],
                'c' => $candleAve['c'],
                'candleAve' => $candleAve['candleAve'],
                'totalTilTen' => $totalTilTen,
                'maDiv' => $maDiv,
                'i' => $i,
                'ma' => $ma,
            ];

            $distanceFromMa = ($candleAve['candleAve'] - $ma) / $ma; //NO THIS NEED TO BE IN %
            $totalDistanceFromMa += $distanceFromMa;

            $i++;
        }

//        Result::create([
//            'pair' =>
//        ]);

        return $ma;
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
}
