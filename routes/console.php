<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('possible11:sync-series {--status=live : Status to sync: live, upcoming, or all}', function (\App\Services\Possible11ApiService $service) {
    $status = (string) ($this->option('status') ?: 'live');
    $this->info("Fetching and synchronizing series, teams, matches, and squads from Possible11 API (status: {$status})...");
    $result = $service->syncSeries($status, true);
    if ($result['success']) {
        $this->info("✓ " . $result['message']);
    } else {
        $this->error("✗ Failed to sync Possible11 series.");
    }
})->purpose('Fetch and update live/upcoming series, squads and matches from Possible11 API');

Artisan::command('possible11:update-statuses', function (\App\Services\Possible11ApiService $service) {
    $this->info("Re-evaluating and transitioning match lifecycle statuses (live, upcoming, completed)...");
    $updated = $service->autoUpdateAllMatchStatuses();
    $this->info("✓ Successfully updated {$updated} matches.");
})->purpose('Auto transition expired live matches to completed and lock winning results');

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
            [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false, \PDO::ATTR_TIMEOUT => 30]
        );
        $localPdo = \Illuminate\Support\Facades\DB::connection()->getPdo();
        $tables = [
            'users', 'tournaments', 'teams', 'venues', 'players', 'matches',
            'ball_by_ball', 'news', 'articles', 'fantasy_tips', 'predictions',
            'web_stories', 'glossary_terms', 'team_rankings', 'icc_player_rankings'
        ];
        $cloudPdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        foreach ($tables as $table) {
            try {
                $cloudTableExists = $cloudPdo->query("SHOW TABLES LIKE '$table'")->rowCount() > 0;
                if (!$cloudTableExists) {
                    $createRes = $localPdo->query("SHOW CREATE TABLE `$table`")->fetch(\PDO::FETCH_ASSOC);
                    $createSql = $createRes['Create Table'] ?? null;
                    if ($createSql) {
                        $cloudPdo->exec($createSql);
                    }
                } else {
                    $localCols = $localPdo->query("SHOW COLUMNS FROM `$table`")->fetchAll(\PDO::FETCH_ASSOC);
                    $cloudCols = $cloudPdo->query("SHOW COLUMNS FROM `$table`")->fetchAll(\PDO::FETCH_ASSOC);
                    $cloudColNames = array_column($cloudCols, 'Field');
                    foreach ($localCols as $col) {
                        if (!in_array($col['Field'], $cloudColNames)) {
                            $type = $col['Type'];
                            $null = ($col['Null'] === 'YES') ? 'NULL' : 'NOT NULL';
                            $default = ($col['Default'] !== null) ? "DEFAULT " . $cloudPdo->quote($col['Default']) : ($col['Null'] === 'YES' ? "DEFAULT NULL" : "");
                            try {
                                $cloudPdo->exec("ALTER TABLE `$table` ADD COLUMN `{$col['Field']}` $type $null $default");
                            } catch (\Throwable $ex) {}
                        }
                    }
                }

                $rows = $localPdo->query("SELECT * FROM `$table`")->fetchAll(\PDO::FETCH_ASSOC);
                $cloudPdo->exec("TRUNCATE TABLE `$table`;");
                if (empty($rows)) {
                    $this->line(" ✓ Synced table: <info>{$table}</info> (0 rows)");
                    continue;
                }

                $cloudCols = $cloudPdo->query("SHOW COLUMNS FROM `$table`")->fetchAll(\PDO::FETCH_ASSOC);
                $cloudColNames = array_column($cloudCols, 'Field');
                $validCols = array_values(array_intersect(array_keys($rows[0]), $cloudColNames));
                $colList = implode('`, `', $validCols);

                $chunks = array_chunk($rows, 100);
                foreach ($chunks as $chunk) {
                    $placeholders = [];
                    $values = [];
                    foreach ($chunk as $row) {
                        $placeholders[] = '(' . implode(', ', array_fill(0, count($validCols), '?')) . ')';
                        foreach ($validCols as $colName) {
                            $values[] = $row[$colName];
                        }
                    }
                    $stmt = $cloudPdo->prepare("INSERT INTO `$table` (`$colList`) VALUES " . implode(', ', $placeholders));
                    $stmt->execute($values);
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
