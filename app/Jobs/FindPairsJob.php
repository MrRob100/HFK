<?php

namespace App\Jobs;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class FindPairsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $candleType;

    public function __construct(string $candleType)
    {
        $this->candleType = $candleType;
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
            return strpos($value->symbol, 'USDT') !== false ? $value : null;
        });

        $checked = 0;
        foreach ($filtered->toArray() as $symbolOuter) {
            foreach ($filtered->toArray() as $symbolInner) {
                $checked++;
                if ($checked % 100 == 0) {

                    $total = $filtered->count() * $filtered->count();

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
            }
        }

        //nested foreach to get all combos, get ohlc and go back 100 and do calcs
        //make a db table with updates about whats going on, poll it with vue
    }
}
