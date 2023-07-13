<?php

namespace App\Console\Commands;

use App\Providers\SitemapServiceProvider as SiteMapGenerator;
use Illuminate\Console\Command;
use Log;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generateSitemap';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates the sitemap';

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
     * Generates the sitemap.
     *
     * @return mixed
     */
    public function handle()
    {
        if (SiteMapGenerator::generateSitemap()) {
            echo '[*]['.date('H:i:s').'] Sitemap generated successfully.';
            Log::channel('cronjobs')->info('[*] Sitemap generated successfully. \r\n');
        } else {
            echo '[*]['.date('H:i:s').'] Sitemap generation failed';
            Log::channel('cronjobs')->info('[*] Sitemap generation failed. \r\n');
        }
        return 0;
    }
}
