<?php

namespace App\Console\Commands;

use App\Jobs\FindPairsJob;
use App\Services\FormatPairService;
use Illuminate\Console\Command;

class FindBinancePairs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:binance';

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
        $formatPairService = app(FormatPairService::class);

        FindPairsJob::dispatch('binance', '1d', $formatPairService);

        return Command::SUCCESS;
    }
}
