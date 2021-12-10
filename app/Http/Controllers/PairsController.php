<?php

namespace App\Http\Controllers;

use App\Models\Pair;
use Illuminate\Http\Request;

class PairsController extends Controller
{
    public function index(Request $request)
    {
        return Pair::where('state', $request->state)->get();
    }

    public function create(Request $request)
    {
        Pair::UpdateOrCreate(
            [
                's1' => $request->all()['params']['s1'],
                's2' => $request->all()['params']['s2'],
            ],
            $request->all()['params']
        );
    }

    public function sync(): void
    {
//        $params = Pair::where('state', 'active')->get()->toArray();
//
//        $curl = curl_init();
//
//        curl_setopt_array($curl, array(
//            CURLOPT_URL => 'https://secure-reef-12042.herokuapp.com/api/sync',
//            CURLOPT_RETURNTRANSFER => true,
//            CURLOPT_ENCODING => '',
//            CURLOPT_MAXREDIRS => 10,
//            CURLOPT_TIMEOUT => 0,
//            CURLOPT_FOLLOWLOCATION => true,
//            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//            CURLOPT_CUSTOMREQUEST => 'POST',
//            CURLOPT_POSTFIELDS => '{
//                "pairs": '. json_encode($params) . '
//            }',
//            CURLOPT_HTTPHEADER => array(
//                'Content-Type: application/json',
//            ),
//        ));
//
//        $response = curl_exec($curl);
//
//        curl_close($curl);
//        dump($response);
    }
}
