<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use KuCoin\SDK\PrivateApi\Account;
use KuCoin\SDK\Exceptions\HttpException;
use KuCoin\SDK\Exceptions\BusinessException;
use KuCoin\SDK\Auth;

class ManualController extends Controller
{
    public function position(Request $request) {
        $auth = new Auth(env('KC_API_KEY'), env('KC_API_SECRET'), env('KC_API_PASSPHRASE'), Auth::API_KEY_VERSION_V2);
        $api = new Account($auth);

        try {
            $result = $api->getList(['type' => 'trade']);

            $price = 1;
            if ($request->of !== 'USDT') {

                $data = json_decode(file_get_contents("https://api.kucoin.com/api/v1/market/orderbook/level1?symbol={$request->of}-USDT"), true);
                if (isset($data['data'])) {
                    $price = json_decode(file_get_contents("https://api.kucoin.com/api/v1/market/orderbook/level1?symbol={$request->of}-USDT"), true)['data']['price'];
                } else {
                    $binance = json_decode(file_get_contents("https://www.binance.com/api/v3/ticker/price?symbol={$request->of}USDT"), true);
                    $price = $binance['price'];
                }
            }

            $amount = collect($result)->where('currency', '=', $request->of)->first();

            return [
                'position_data' => $amount,
                'qty' => $amount['balance'],
                'market_value' => $amount['balance'] * $price,
                'price' => $price,
            ];
        } catch (HttpException $e) {
            dd($e->getMessage());
        } catch (BusinessException $e) {
            dd($e->getMessage());
        }
    }
}
