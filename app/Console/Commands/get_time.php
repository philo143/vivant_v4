<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Events\ServerTime;

class get_time extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:time';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get the servers time';

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
     * @return mixed
     */
    public function handle()
    {
        $time = Date('H:i');
        event(new ServerTime($time));
    }
}
