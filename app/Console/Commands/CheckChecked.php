<?php

namespace App\Console\Commands;

use App\Models\Result;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CheckChecked extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:checked';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (Cache::has('all')) {
            $data = Cache::get('all');
        } else {
            $data = collect(json_decode(file_get_contents('https://api3.binance.com/api/v3/ticker/24hr')));

            Cache::put('all', $data, 600);
        }

        $letter = 'C';

        $filtered = $data->filter(function ($value, $key) use ($letter) {

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
                && !str_contains($value->symbol, 'USDC')
                && !str_contains($value->symbol, 'TUSD')
                && !str_contains($value->symbol, 'BUSD')
                && str_contains($value->symbol, 'USDT')
                && strpos($value->symbol, $letter) !== false //to slim down while testing
            ) {
                return $value;
            }
        });

        $total = $filtered->count() * $filtered->count();

        dump("total: $total");

//        $known_not_checked = file_get_contents()

        $i = 0;
        $not_checked = 0;
        $not_checked_arr = [];
        foreach ($filtered->toArray() as $symbolOuter) {
            foreach ($filtered->toArray() as $symbolInner) {

                if ($symbolOuter->symbol !== $symbolInner->symbol) {

                    $symbols = [
                        's1' => str_replace('USDT', '', $symbolOuter->symbol),
                        's2' => str_replace('USDT', '', $symbolInner->symbol),
                    ];

                    if (
                        Result::where('symbol1', $symbols['s1'])->where('symbol2', $symbols['s2'])->get()->isEmpty()
                        && Result::where('symbol1', $symbols['s2'])->where('symbol2', $symbols['s1'])->get()->isEmpty()
                    ) {
                        dump('not done' . $symbols['s1'] . 'x' . $symbols['s2']);

//                        array_push($not_checked_arr, ['']$symbols['s1'].$symbols['s2']);

                        array_push($not_checked_arr, [
                            'symbol1' => $symbols['s1'],
                            'symbol2' => $symbols['s2'],
                        ]);

                        $not_checked++;
                    }
                }

                $i++;
                dump('checked' . $i);

                //needed other break
//                if ($not_checked > 100) {
//                    break;
//                }
            }
        }

        dump($not_checked);

        file_put_contents(public_path() . '/not_checked_' . $letter . '.json', json_encode($not_checked_arr));

        return Command::SUCCESS;
    }
}
