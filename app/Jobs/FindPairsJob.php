<?php

namespace App\Jobs;

use App\Models\Message;
use App\Models\Result;
use App\Models\Truth;
use App\Services\FormatPairService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FindPairsJob
//class FindPairsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $candleType;

    protected $formatPairsService;

    public function __construct(string $candleType, FormatPairService $formatPairsService)
    {
        $this->candleType = $candleType;
        $this->formatPairsService = $formatPairsService;
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
        });

        $checked = 0;
//        foreach ($filtered->toArray() as $symbolOuter) {
//
//            $dataOuter = $this->formatPairService->getCandlesData($symbolOuter->symbol, $this->candleType);
//
//            foreach ($filtered->toArray() as $symbolInner) {
//
//                if ($symbolOuter->symbol !== $symbolInner->symbol) {
//                    $dataInner = $this->formatPairService->getCandlesData($symbolInner->symbol, $this->candleType);
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
        $akro = $this->formatPairsService->getCandlesData('AKROUSDT', $this->candleType);
        $nuls = $this->formatPairsService->getCandlesData('NULSUSDT', $this->candleType);

        $akronuls = $this->formatPairsService->createPairData($akro, $nuls);

        $akronuls_result = $this->performCalcs($akronuls, $this->candleType, 'AKROUSDT', 'NULSUSDT');
    }

    public function performCalcs($pairData, $candleType, $symbol1, $symbol2)
    {
        //first we want to truth it
        //then we want to give it a score

        // IMPLEMENT THIS
        //n is period - make it 25

        $period = 25;

        //EMA = (Close - previous EMA) * (2 / n+1) + previous EMA

        //set all in [] then loop through those again to calc dists from MA etc

        $i = 0;
        $calced = [];
        $middles = 0;
        $oneup = 0;
        $twoup = 0;
        $threeup = 0;
        $fourup = 0;
        $fiveup = 0;
        $sixup = 0;
        $sevenup = 0;
        $eightup = 0;
        $nineup = 0;
        $tenup = 0;

        $onedown = 0;
        $twodown = 0;
        $threedown = 0;
        $fourdown = 0;
        $fivedown = 0;
        $sixdown = 0;
        $sevendown = 0;
        $eightdown = 0;
        $ninedown = 0;
        $tendown = 0;

        foreach ($pairData as $item) {
            if ($i === 0) {

                $ema = $item[4];

                $calced[] = [
                    'o' => $item[1],
                    'h' => $item[2],
                    'l' => $item[3],
                    'c' => $item[4],
                    'ema' => $ema,
                ];
            } else {
                $previousMA = $calced[$i - 1]['ema'];

                $ema = ($item[4] - $previousMA) * (2 / ($period + 1)) + $previousMA;

                $calced[] = [
                    'o' => $item[1],
                    'h' => $item[2],
                    'l' => $item[3],
                    'c' => $item[4],
                    'ema' => $ema,
                ];

                $oneperc = $ema * 0.01;
                $twoperc = $ema * 0.02;
                $threeperc = $ema * 0.03;
                $fourperc = $ema * 0.04;
                $fiveperc = $ema * 0.05;
                $sixperc = $ema * 0.06;
                $sevenperc = $ema * 0.07;
                $eightperc = $ema * 0.08;
                $nineperc = $ema * 0.09;
                $tenperc = $ema * 0.1;

                $close = $item[4];

                //middle
                if ($close < ($ema + $oneperc) && $close > ($ema - $oneperc)) {
                    $middles++;
                }

                //ups
                if ($close > ($ema + $oneperc) && $close < ($ema + $twoperc)) {
                    $oneup++;
                }
                if ($close > ($ema + $twoperc) && $close < ($ema + $threeperc)) {
                    $twoup++;
                }
                if ($close > ($ema + $threeperc) && $close < ($ema + $fourperc)) {
                    $threeup++;
                }
                if ($close > ($ema + $fourperc) && $close < ($ema + $fiveperc)) {
                    $fourup++;
                }
                if ($close > ($ema + $fiveperc) && $close < ($ema + $sixperc)) {
                    $fiveup++;
                }
                if ($close > ($ema + $sixperc) && $close < ($ema + $sevenperc)) {
                    $sevenup++;
                }
                if ($close > ($ema + $sevenperc) && $close < ($ema + $eightperc)) {
                    $eightup++;
                }
                if ($close > ($ema + $eightperc) && $close < ($ema + $nineperc)) {
                    $nineup++;
                }

                if ($close > ($ema + $tenperc)) {
                    $tenup++;
                }

                //downs
                if ($close < ($ema - $oneperc) && $close > ($ema - $twoperc)) {
                    $onedown++;
                }

                if ($close < ($ema - $twoperc) && $close > ($ema - $threeperc)) {
                    $twodown++;
                }
                if ($close < ($ema - $threeperc) && $close > ($ema - $fourperc)) {
                    $threedown++;
                }
                if ($close < ($ema - $fourperc) && $close > ($ema - $fiveperc)) {
                    $fourdown++;
                }
                if ($close < ($ema - $fiveperc) && $close > ($ema - $sixperc)) {
                    $fivedown++;
                }
                if ($close < ($ema - $sixperc) && $close > ($ema - $sevenperc)) {
                    $sixdown++;
                }
                if ($close < ($ema - $sevenperc) && $close > ($ema - $eightperc)) {
                    $sevendown++;
                }
                if ($close < ($ema - $eightperc) && $close > ($ema - $nineperc)) {
                    $eightdown++;
                }
                if ($close < ($ema - $nineperc) && $close > ($ema - $tenperc)) {
                    $ninedown++;
                }
                if ($close < ($ema - $tenperc)) {
                    $tendown++;
                }

//                dump($close);
//                dump($ema);
//                dump($ema + $oneperc);
//                dump($ema + $twoperc);
//                die();
            }

            $truth = Truth::whereUnix($item[0])->wherePair('akronuls')->get();

            if ($truth->isEmpty() && $symbol1 === 'AKROUSDT' && $symbol2 === 'NULSUSDT') {
                Truth::create(
                    [
                        'pair' => 'akronuls',
                        'unix' => $item[0],
                        'o' => $item[1],
                        'h' => $item[2],
                        'l' => $item[3],
                        'c' => $item[4],
                        'ema' => $ema,
                    ]
                );
            }

            $i++;
        }

        Result::create([
            'symbol1' => $symbol1,
            'symbol2' => $symbol2,
            'candle_type' => $candleType,
            'middles' => $middles,
            'oneup' => $oneup,
            'twoup' => $twoup,
            'threeup' => $threeup,
            'fourup' => $fourup,
            'fiveup' => $fiveup,
            'sixup' => $sixup,
            'sevenup' => $sevenup,
            'eightup' => $eightup,
            'nineup' => $nineup,
            'tenup' => $tenup,
            'onedown' => $onedown,
            'twodown' => $twodown,
            'threedown' => $threedown,
            'fourdown' => $fourdown,
            'fivedown' => $fivedown,
            'sixdown' => $sixdown,
            'sevendown' => $sevendown,
            'eightdown' => $eightdown,
            'ninedown' => $ninedown,
            'tendown' => $tendown,
        ]);

        return [];
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
