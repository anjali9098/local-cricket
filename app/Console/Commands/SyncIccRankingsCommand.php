<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Possible11RankingService;

class SyncIccRankingsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cricket:sync-icc-rankings 
                            {--limit=100 : Number of ranks per category and format (default: 100 to fetch all 50)}
                            {--url= : Optional specific Possible11 ranking API URL}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize official ICC Team and Player Rankings across TEST, ODI, and T20 from Possible11 API';

    /**
     * Execute the console command.
     */
    public function handle(Possible11RankingService $service): int
    {
        $limit = (int) ($this->option('limit') ?: 100);
        $url = trim((string) $this->option('url'));

        if (!empty($url)) {
            $this->info("🌐 Fetching ranking payload from specific URL: {$url}");
            try {
                $res = $service->syncUrl($url, $limit);
                $this->info("✓ " . $res['message']);
                return Command::SUCCESS;
            } catch (\Throwable $e) {
                $this->error("✗ Failed to sync URL: " . $e->getMessage());
                return Command::FAILURE;
            }
        }

        $this->info("🏏 Starting full synchronization of all 12 Possible11 ICC Ranking endpoints (limit: {$limit})...");

        $report = $service->syncAll($limit, function ($msg) {
            $this->line($msg);
        });

        $this->newLine();
        $this->info("==================================================================");
        $this->info("✓ ICC Rankings Synchronization Complete!");
        $this->info("• Synced Endpoints: {$report['synced_endpoints']} / {$report['total_endpoints']}");
        $this->info("• Total Teams: {$report['total_teams']}");
        $this->info("• Total Players: {$report['total_players']}");

        if (!empty($report['errors'])) {
            $this->warn("⚠ Errors encountered: " . count($report['errors']));
            foreach ($report['errors'] as $err) {
                $this->error("  " . $err);
            }
        }
        $this->info("==================================================================");

        return Command::SUCCESS;
    }
}
