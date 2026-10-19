<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\HeadToHeadScraperService;

class SyncHeadToHeadCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cricket:sync-head-to-head 
                            {--format=all : Format to sync: test, odi, t20, all}
                            {--limit=0 : Limit number of matchups per format (0 = unlimited)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape and synchronize international cricket Head to Head records (Won, Lost, Drawn, Tied, Home/Away) from MyKhel into database using loop engineering';

    /**
     * Execute the console command.
     */
    public function handle(HeadToHeadScraperService $service): int
    {
        $format = $this->option('format') ?: 'all';
        $limit = (int) $this->option('limit');

        $this->info("🏏 Starting Head to Head web scraper for format: [{$format}] (limit per format: {$limit})...");

        $res = $service->syncFromMyKhel($format, $limit, function($msg) {
            $this->line($msg);
        });

        $this->newLine();
        $this->info("=================================================");
        $this->info("✓ Successfully synced {$res['synced']} Head-to-Head records!");
        if ($res['errors'] > 0) {
            $this->warn("⚠ Encountered {$res['errors']} errors during scraping.");
        }
        $this->info("=================================================");

        return Command::SUCCESS;
    }
}
