<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class BinanceGetService {

    public function apiCall($symbol, $interval = "1d")
    {
        if (Cache::has($symbol.$interval)) {
            $response = Cache::get($symbol.$interval);
        } else {
            $response = array_reverse(json_decode(file_get_contents("https://www.binance.com/api/v3/klines?symbol={$symbol}USDT&interval={$interval}"), true));

            Cache::put($symbol.$interval, $response, 60);
        }

        return $response;
    }

    public function price($symbol)
    {
        $data = json_decode(file_get_contents("https://www.binance.com/api/v3/ticker/price?symbol={$symbol}USDT"), true);

        return $data['price'];
    }

    public function getIcon($symbol): ?string
    {
        //gxs doesnt work

        $coinData = json_decode(file_get_contents(public_path('cmc.json')), true);

        $first = collect($coinData['data'])->where('symbol', '=', $symbol)->first();

        if ($first) {
            $url = "https://s2.coinmarketcap.com/static/img/coins/64x64/{$first['id']}.png";

            $file_name = public_path() . '/icons/' . $symbol . '.png';

            if (!file_exists($file_name)) {
                $ch = curl_init($url);
                $fp = fopen(public_path() . '/icons/' . $symbol . '.png' , 'wb');
                curl_setopt($ch, CURLOPT_FILE, $fp);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_exec($ch);
                curl_close($ch);
                fclose($fp);
            }

            return '/icons/' . $symbol . '.png';
        } else {
            return false;
        }
    }
}
