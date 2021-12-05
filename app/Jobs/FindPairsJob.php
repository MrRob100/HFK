<?php

namespace App\Jobs;

use App\Models\KucoinResult;
use App\Models\Result;
use App\Services\FormatPairService;
use Carbon\Carbon;
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

    protected $exchange;

    protected $candleType;

    protected $formatPairsService;

    public function __construct(string $exchange, string $candleType, FormatPairService $formatPairsService)
    {
        $this->exchange = $exchange;
        $this->candleType = $candleType;
        $this->formatPairsService = $formatPairsService;
    }

    public function handle()
    {
        if ($this->exchange === 'binance') {
            if (Cache::has('all')) {
                $data = Cache::get('all');
            } else {
                $data = collect(json_decode(file_get_contents('https://api3.binance.com/api/v3/ticker/24hr')));

                Cache::put('all', $data, 600);
            }
        } elseif ($this->exchange === 'kucoin') {

            if (Cache::has('allkucoin')) {
                $data = Cache::get('allkucoin');
            } else {
                $data = collect(json_decode(file_get_contents('https://api.kucoin.com/api/v1/symbols'))->data);

                Cache::put('allkucoin', $data, 600);
            }

        } else {
            dd('no exchange');
        }

        $filtered = $data->filter(function ($value, $key) {

            if (
                !str_contains($value->symbol, 'DOWN')
                && !str_contains($value->symbol, 'BULL')
                && !str_contains($value->symbol, 'BEAR')
                && !str_contains($value->symbol, 'BTC')
                && !str_contains($value->symbol, 'ETH')
                && !str_contains($value->symbol, 'DOGE')
                && !str_contains($value->symbol, 'MITH')
                && !str_contains($value->symbol, 'EUR')
                && !str_contains($value->symbol, 'GBP')
                && !str_contains($value->symbol, 'AUD')
                && !str_contains($value->symbol, 'DAI')
                && !str_contains($value->symbol, 'RUB')
                && !str_contains($value->symbol, 'USDC')
                && !str_contains($value->symbol, 'TUSD')
                && !str_contains($value->symbol, 'BUSD')
                && !str_contains($value->symbol, 'SUSD')
                && !str_contains($value->symbol, 'DAI')
                && !str_contains($value->symbol, 'BVND')
                && !str_contains($value->symbol, 'IDRT')
                && !str_contains($value->symbol, 'UAH')
                && !str_contains($value->symbol, 'RUB')
                && !str_contains($value->symbol, 'BIDR')
                && str_contains($value->symbol, 'USDT')
//                && str_contains($value->symbol, 'AK')//slimmed
            ) {
                return $value;
            }
        });

        $filtered2 = $filtered->filter(function ($value, $key) {
            if (str_contains($value->symbol, 'AK')) {
                return $value;
            }
        });


//        $filtered2 = $filtered;

        dump('total: ' . $filtered->count() * $filtered2->count());

        $i = 0;

        foreach ($filtered->toArray() as $symbolOuter) {

            $dataOuter = $this->formatPairsService->getCandlesData($symbolOuter->symbol, $this->candleType, $this->exchange);

            foreach ($filtered2->toArray() as $symbolInner) {

                if ($symbolOuter->symbol !== $symbolInner->symbol) {

                    if ($this->candleType === '1d') {
                        if ($this->exchange === 'binance') {

                            $symbols = [
                                's1' => str_replace('USDT', '', $symbolOuter->symbol),
                                's2' => str_replace('USDT', '', $symbolInner->symbol),
                            ];

                            if (
                                Result::where('symbol1', $symbols['s1'])->where('symbol2', $symbols['s2'])->get()->isEmpty()
                                && Result::where('symbol1', $symbols['s2'])->where('symbol2', $symbols['s1'])->get()->isEmpty()
                            ) {
                                $dataInner = $this->formatPairsService->getCandlesData($symbolInner->symbol, $this->candleType, 'binance');
                                $this->dry($symbolInner->symbol, $symbolOuter->symbol, $dataOuter, $dataInner);
                            }
                        } elseif ($this->exchange === 'kucoin') {

                            $symbols = [
                                's1' => str_replace('-USDT', '', $symbolOuter->symbol),
                                's2' => str_replace('-USDT', '', $symbolInner->symbol),
                            ];

                            if (
                                KucoinResult::where('symbol1', $symbols['s1'])->where('symbol2', $symbols['s2'])->get()->isEmpty()
                                && KucoinResult::where('symbol1', $symbols['s2'])->where('symbol2', $symbols['s1'])->get()->isEmpty()
                            ) {
                                $dataInner = $this->formatPairsService->getCandlesData($symbolInner->symbol, $this->candleType, 'kucoin');
                                $this->dry($symbolInner->symbol, $symbolOuter->symbol, $dataOuter, $dataInner);
                            }
                        }
                    }
                }
                $i++;
                if ($i % 100 === 0) {
                    dump('checked ' . $i);
                }
            }
        }


        if ($this->exchange === 'kucoin') {

            dump('exchange is kucoin');

            if (
                KucoinResult::where('symbol1', 'BNB')->where('symbol2', 'DOT')->get()->isEmpty()
                && KucoinResult::where('symbol1', 'DOT')->where('symbol2', 'BNB')->get()->isEmpty()
            ) {


                dump('table empty');

                $dataOuter = $this->formatPairsService->getCandlesData('BNB-USDT', $this->candleType, 'kucoin');
                $dataInner = $this->formatPairsService->getCandlesData('DOT-USDT', $this->candleType, 'kucoin');

                $this->dry('BNB-USDT', 'DOT-USDT', $dataOuter, $dataInner, true);
            }
        }
    }

    public function dry(string $symbolInner, string $symbolOuter, $dataOuter, $dataInner, $timesHun = false)
    {
        $pairData = $this->formatPairsService->createPairData($dataOuter, $dataInner, $timesHun);

        if (sizeof($pairData) > 88) {
            dump('add to db ' . $symbolInner . 'x' . $symbolOuter);
            $results = $this->performCalcs($pairData, $this->candleType, $symbolInner, $symbolOuter);
        }
    }

    public function performCalcs($pairData, $candleType, $symbol1, $symbol2)
    {
        $i = 0;
        $calced = [];

        $usn_25_50 = 0;

        foreach ($pairData as $item) {
            if ($i === 0) {

                $ema25 = $item[4];

                $calced[] = [
                    'o' => $item[1],
                    'h' => $item[2],
                    'l' => $item[3],
                    'c' => $item[4],
                    'ema_25' => $ema25,
                    'ema_50' => $ema25,
                ];
            } else {
                $previousMA25 = $calced[$i - 1]['ema_25'];
                $previousMA50 = $calced[$i - 1]['ema_50'];

                $ema25 = ($item[4] - $previousMA25) * (2 / (25 + 1)) + $previousMA25;

                $ema50 = ($item[4] - $previousMA50) * (2 / (50 + 1)) + $previousMA50;

                $calced[] = [
                    'o' => $item[1],
                    'h' => $item[2],
                    'l' => $item[3],
                    'c' => $item[4],
                    'ema_25' => $ema25,
                    'ema_50' => $ema50,
                ];

                $close = $item[4];

                //un-straight-ness (usn)
                $diff = $ema25 - $ema50;
                if ($diff < 0) {
                    $unsigned_diff = -$diff;
                } else {
                    $unsigned_diff = $diff;
                }
                $usn_25_50 += floor(($unsigned_diff / $ema50) * 100);
            }

            $i++;
        }

        if ($this->exchange === 'binance') {
            Result::create([
                'symbol1' => str_replace('USDT', '', $symbol1),
                'symbol2' => str_replace('USDT', '', $symbol2),
                'candle_type' => $candleType,
                'start' => Carbon::createFromTimestamp($pairData[0][0] / 1000)->toDate(),
                'end' => Carbon::createFromTimestamp($pairData[sizeof($pairData) - 1][0] / 1000)->toDate(),
                'usn_25_50' => $usn_25_50,
            ]);
        } elseif ($this->exchange === 'kucoin') {
            KucoinResult::create([
                'symbol1' => str_replace('-USDT', '', $symbol1),
                'symbol2' => str_replace('-USDT', '', $symbol2),
                'candle_type' => $candleType,
                'start' => Carbon::createFromTimestamp($pairData[0][0] / 1000)->toDate(),
                'end' => Carbon::createFromTimestamp($pairData[sizeof($pairData) - 1][0] / 1000)->toDate(),
                'usn_25_50' => $usn_25_50,
            ]);
        } else {
            dd('neither');
        }

        return [];
    }
}
