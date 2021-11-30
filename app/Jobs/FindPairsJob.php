<?php

namespace App\Jobs;

use App\Models\Hour;
use App\Models\Message;
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
                && str_contains($value->symbol, 'USDT')
            ) {
                return $value;
            }
        });

        $filtered2 = $data->filter(function ($value, $key) {
            if (
                !str_contains($value->symbol, 'DOWN')
                && !str_contains($value->symbol, 'BULL')
                && !str_contains($value->symbol, 'BEAR')
                && !str_contains($value->symbol, 'BTC')
                && !str_contains($value->symbol, 'ETH')
                && !str_contains($value->symbol, 'DOGE')
                && !str_contains($value->symbol, 'MITH')
                && !str_contains($value->symbol, 'RUB')
                && !str_contains($value->symbol, 'EUR')
                && !str_contains($value->symbol, 'GBP')
                && !str_contains($value->symbol, 'AUD')
                && !str_contains($value->symbol, 'DAI')
                && !str_contains($value->symbol, 'USDC')
                && !str_contains($value->symbol, 'TUSD')
                && !str_contains($value->symbol, 'BUSD')
                && !str_contains($value->symbol, 'SUSD')
                && !str_contains($value->symbol, 'DAI')
                && str_contains($value->symbol, 'USDT')
                && strpos($value->symbol, 'Z') !== false //to slim down while testing
            ) {
                return $value;
            }
        });

        dump('total: ' . $filtered->count() * $filtered2->count());

        $i = 0;

        foreach ($filtered->toArray() as $symbolOuter) {

            $dataOuter = $this->formatPairsService->getCandlesData($symbolOuter->symbol, $this->candleType);

            foreach ($filtered2->toArray() as $symbolInner) {

                if ($symbolOuter->symbol !== $symbolInner->symbol) {

                    $symbols = [
                        's1' => str_replace('USDT', '', $symbolOuter->symbol),
                        's2' => str_replace('USDT', '', $symbolInner->symbol),
                    ];

                    if ($this->candleType === '1d') {
                        if (
                            Result::where('symbol1', $symbols['s1'])->where('symbol2', $symbols['s2'])->get()->isEmpty()
                            && Result::where('symbol1', $symbols['s2'])->where('symbol2', $symbols['s1'])->get()->isEmpty()
                        ) {

                            $dataInner = $this->formatPairsService->getCandlesData($symbolInner->symbol, $this->candleType);

                            $pairData = $this->formatPairsService->createPairData($dataOuter, $dataInner);

                            if (sizeof($pairData) > 88) {
                                dump('add to db ' . $symbols['s1'] . 'x' . $symbols['s2']);
                                $results = $this->performCalcs($pairData, $this->candleType, $symbolInner->symbol, $symbolOuter->symbol);
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

        //get akronuls

        if (
            Result::where('symbol1', 'AKRO')->where('symbol2', 'NULS')->get()->isEmpty()
            && Result::where('symbol1', 'NULS')->where('symbol2', 'AKRO')->get()->isEmpty()
        ) {
            $akro = $this->formatPairsService->getCandlesData('AKROUSDT', $this->candleType);
            $nuls = $this->formatPairsService->getCandlesData('NULSUSDT', $this->candleType);

            $akronuls = $this->formatPairsService->createPairData($akro, $nuls);

            $akronuls_result = $this->performCalcs($akronuls, $this->candleType, 'AKROUSDT', 'NULSUSDT');
        }
    }

    public function performCalcs($pairData, $candleType, $symbol1, $symbol2)
    {
        //first we want to truth it
        //then we want to give it a score

        // IMPLEMENT THIS
        //n is period - make it 25

        //EMA = (Close - previous EMA) * (2 / n+1) + previous EMA

        //set all in [] then loop through those again to calc dists from MA etc

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

        $s1 = str_replace('USDT', '', $symbol1);
        $s2 = str_replace('USDT', '', $symbol2);

        Result::create([
            'symbol1' => $s1,
            'symbol2' => $s2,
            'candle_type' => $candleType,
            'start' => Carbon::createFromTimestamp($pairData[0][0] / 1000)->toDate(),
            'end' => Carbon::createFromTimestamp($pairData[sizeof($pairData) - 1][0] / 1000)->toDate(),
            'usn_25_50' => $usn_25_50,
        ]);

        return [];
    }
}
