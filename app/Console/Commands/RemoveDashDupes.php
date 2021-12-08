<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RemoveDashDupes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'remove:dashdupes';

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
        $files = scandir(public_path() . '/data');

        $nodash = [];
        foreach ($files as $file) {
            if (str_contains($file, '-')) {
                $nodash[] = $file;
            }
        }

        foreach ($files as $file) {
            if (in_array($file, $nodash)) {
                unlink(public_path() . '/data/' . $file);
            }
        }

        return Command::SUCCESS;
    }
}
