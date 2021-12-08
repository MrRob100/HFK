<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SortTimes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:dates';

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

        foreach ($files as $file) {
            if (str_contains($file, '.json')) {

                $data = json_decode(file_get_contents(public_path() . '/data/' . $file), true);

                if (array_key_exists(0, $data)) {
                    if (is_string($data[0][0])) {

                        if (strlen($data[0][0]) == 10) {
                            unlink(public_path() . '/data/' . $file);
                        }
                    }
                }
            }
        }

        return Command::SUCCESS;
    }
}
