<?php

namespace App\Console\Commands;

use App\Models\Message;
use App\Models\Result;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class dedupe extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'de:dupe';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'De dupes results';

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
        $rs = Result::all()->pluck('symbol1')->unique();

        $checked = 0;
        foreach ($rs as $symbolOuter) {
            foreach ($rs as $symbolInner) {

                if ($checked % 100 === 0) {
                    dump("checked $checked");
                }

                $result = Result::where('symbol1', $symbolOuter)->where('symbol2', $symbolInner);

                if ($result->get()->count() > 1) {
                    $result->first()->delete();
                    dump('deleted, i= '. $checked);
                }
                $checked++;
            }
        }

        return Command::SUCCESS;
    }
}
