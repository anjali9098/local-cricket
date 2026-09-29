<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Possible11ApiService;

class SyncPossible11Series extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'possible11:sync-series 
                            {--status=live : Status of series to sync: live, upcoming, completed, all}
                            {--sport=Cricket : Sport: Cricket, Football, etc.}
                            {--id= : Specific series ID to sync}
                            {--squads : Also sync team player squads}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize series, teams, matches, and squads from Possible11 API into MySQL database';

    /**
     * Execute the console command.
     */
    public function handle(Possible11ApiService $service): int
    {
        $this->info('Starting Possible11 API synchronization...');

        $seriesId = $this->option('id');
        $syncSquads = (bool)$this->option('squads');

        if (!empty($seriesId)) {
            $this->info("Synchronizing specific series ID: {$seriesId}...");
            $res = $service->syncSingleSeries((int)$seriesId, $syncSquads);
            if ($res['success']) {
                $this->info("SUCCESS: " . $res['message']);
                return Command::SUCCESS;
            } else {
                $this->error("FAILED: " . $res['message']);
                return Command::FAILURE;
            }
        }

        $status = $this->option('status') ?: 'live';
        $sport = $this->option('sport') ?: 'Cricket';

        $this->info("Fetching and synchronizing status: [{$status}] for sport: [{$sport}] (Squads: " . ($syncSquads ? 'YES' : 'NO') . ")...");
        $res = $service->syncSeries($status, $syncSquads, $sport);

        if ($res['success']) {
            $this->info("SUCCESS: " . $res['message']);
            if (!empty($res['stats'])) {
                $this->table(
                    ['Metric', 'Count'],
                    [
                        ['Series Fetched', $res['stats']['series_fetched']],
                        ['Series Created', $res['stats']['series_created']],
                        ['Series Updated', $res['stats']['series_updated']],
                        ['Teams Synced', $res['stats']['teams_synced']],
                        ['Matches Synced', $res['stats']['matches_synced']],
                        ['Players Synced', $res['stats']['players_synced']],
                    ]
                );
            }
            return Command::SUCCESS;
        }

        $this->error("Sync failed.");
        return Command::FAILURE;
    }
}
