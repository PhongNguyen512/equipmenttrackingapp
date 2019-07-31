<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DailyTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rToken:delete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete revoked token daily';

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
        DB::table('oauth_access_tokens')
            ->where('revoked', '=', '1')
            ->delete();

        DB::table('oauth_refresh_tokens')
            ->where('revoked', '=', '1')
            ->delete();

    }
}
