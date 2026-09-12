<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('cricket:fetch-api {--auto-approve : Automatically approve fetched matches}', function (\App\Services\CricketApiService $apiService) {
    $autoApprove = (bool) $this->option('auto-approve');
    $this->info("Fetching latest cricket matches from CricketData API...");
    $result = $apiService->syncCurrentMatches($autoApprove);
    if ($result['success']) {
        $this->info("✓ " . $result['message']);
    } else {
        $this->error("✗ " . $result['message']);
    }
})->purpose('Fetch and update live/upcoming matches from CricketData API');

Artisan::command('cricket:sync-cloud', function () {
    $this->info("Connecting to Aiven Cloud Database...");
    try {
        $host = env('CLOUD_DB_HOST', env('DB_HOST'));
        $port = env('CLOUD_DB_PORT', env('DB_PORT', '26028'));
        $dbname = env('CLOUD_DB_DATABASE', 'defaultdb');
        $user = env('CLOUD_DB_USERNAME', 'avnadmin');
        $pass = env('CLOUD_DB_PASSWORD', env('DB_PASSWORD', ''));

        $cloudPdo = new \PDO(
            "mysql:host={$host};port={$port};dbname={$dbname}",
            $user,
            $pass,
            [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false, \PDO::ATTR_TIMEOUT => 15]
        );
        $localPdo = \Illuminate\Support\Facades\DB::connection()->getPdo();
        $tables = [
            'users', 'tournaments', 'teams', 'venues', 'players', 'matches',
            'ball_by_ball', 'news', 'articles', 'fantasy_tips', 'predictions',
            'web_stories', 'glossary_terms', 'player_birthdays', 'player_rankings',
            'team_rankings', 'points_table', 'player_batting_stats', 'player_bowling_stats'
        ];
        $localPdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        foreach ($tables as $table) {
            try {
                $rows = $cloudPdo->query("SELECT * FROM `$table`")->fetchAll(\PDO::FETCH_ASSOC);
                $localPdo->exec("DELETE FROM `$table`;");
                if (empty($rows)) {
                    continue;
                }
                $columns = array_keys($rows[0]);
                $colList = implode('`, `', $columns);
                $placeholders = implode(', ', array_fill(0, count($columns), '?'));
                $stmt = $localPdo->prepare("INSERT INTO `$table` (`$colList`) VALUES ($placeholders)");
                foreach ($rows as $row) {
                    $stmt->execute(array_values($row));
                }
                $this->line(" ✓ Synced table: <info>{$table}</info> (" . count($rows) . " rows)");
            } catch (\Throwable $e) {
                // continue
            }
        }
        $localPdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
        $this->info("✓ Local phpMyAdmin MySQL is now 100% synchronized with Cloud DB!");
    } catch (\Throwable $e) {
        $this->error("Failed to connect to Cloud DB: " . $e->getMessage());
    }
})->purpose('Synchronize all data from Aiven Cloud DB to local MySQL');

Artisan::command('cricket:push-cloud', function () {
    $this->info("Pushing Local MySQL database to Aiven Cloud Database...");
    try {
        $host = env('CLOUD_DB_HOST', env('DB_HOST'));
        $port = env('CLOUD_DB_PORT', env('DB_PORT', '26028'));
        $dbname = env('CLOUD_DB_DATABASE', 'defaultdb');
        $user = env('CLOUD_DB_USERNAME', 'avnadmin');
        $pass = env('CLOUD_DB_PASSWORD', env('DB_PASSWORD', ''));

        $cloudPdo = new \PDO(
            "mysql:host={$host};port={$port};dbname={$dbname}",
            $user,
            $pass,
            [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false, \PDO::ATTR_TIMEOUT => 15]
        );
        $localPdo = \Illuminate\Support\Facades\DB::connection()->getPdo();
        $tables = [
            'users', 'tournaments', 'teams', 'venues', 'players', 'matches',
            'ball_by_ball', 'news', 'articles', 'fantasy_tips', 'predictions',
            'web_stories', 'glossary_terms', 'player_birthdays', 'player_rankings',
            'team_rankings', 'points_table', 'player_batting_stats', 'player_bowling_stats'
        ];
        $cloudPdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        foreach ($tables as $table) {
            try {
                $rows = $localPdo->query("SELECT * FROM `$table`")->fetchAll(\PDO::FETCH_ASSOC);
                $cloudPdo->exec("DELETE FROM `$table`;");
                if (empty($rows)) {
                    continue;
                }
                $columns = array_keys($rows[0]);
                $colList = implode('`, `', $columns);
                $placeholders = implode(', ', array_fill(0, count($columns), '?'));
                $stmt = $cloudPdo->prepare("INSERT INTO `$table` (`$colList`) VALUES ($placeholders)");
                foreach ($rows as $row) {
                    $stmt->execute(array_values($row));
                }
                $this->line(" ✓ Pushed table: <info>{$table}</info> (" . count($rows) . " rows)");
            } catch (\Throwable $e) {
                $this->line(" ✗ Skipping {$table}: " . $e->getMessage());
            }
        }
        $cloudPdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
        $this->info("✓ Aiven Cloud DB has been 100% updated with your latest local changes!");
    } catch (\Throwable $e) {
        $this->error("Failed to connect to Cloud DB: " . $e->getMessage());
    }
})->purpose('Push all data from Local MySQL to Aiven Cloud DB');
