<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Filesystem\Filesystem;
class CronClearCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:clear_cache_files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clears old session fies, keeping server files quota reduced';

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
     * Clears old session fies, keeping server files quota reduced.
     *
     * @return mixed
     */
    public function handle()
    {
        Artisan::call('view:clear');
        Artisan::call('cache:clear');

        $file = new Filesystem;
        $file->cleanDirectory(storage_path('app').'/tmp');
        $file->cleanDirectory(storage_path('app').'/chunks');

        Log::channel('cronjobs')->info('[*]['.date('H:i:s')."] Cached files cleared.\r\n");
        return 0;
    }
}
