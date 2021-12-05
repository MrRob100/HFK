<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class KucoinGetService {

    public function apiCall($symbol, $interval = '1d')
    {
        if (Cache::has($symbol.$interval.'kucoin')) {
            $response = Cache::get($symbol.$interval.'kucoin');
        } else {
            if ($interval = '1d') {
                $candleTypeKucoin = '1day';

                $startTime = Carbon::now()->subYear()->unix(); //could make this deeper easily

                $response = json_decode(file_get_contents("https://api.kucoin.com/api/v1/market/candles?type={$candleTypeKucoin}&startAt={$startTime}&symbol={$symbol}-USDT"), true)['data'];

                Cache::put($symbol.$interval.'kucoin', $response, 600);
            } else {
                dd('not supported');
            }
        }

        return $response;
    }
}
