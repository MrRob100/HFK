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
        // Auth version v2 (recommend)
        $auth = new Auth(env('KC_API_KEY'), env('KC_API_SECRET'), env('KC_API_PASSPHRASE'), Auth::API_KEY_VERSION_V2);
        // Auth version v1
        // $auth = new Auth('key', 'secret', 'passphrase');

        $api = new Account($auth);

        try {
            $result = $api->getList(['type' => 'trade']);
            dd($result);
        } catch (HttpException $e) {
            dd($e->getMessage());
        } catch (BusinessException $e) {
            dd($e->getMessage());
        }
    }
}
