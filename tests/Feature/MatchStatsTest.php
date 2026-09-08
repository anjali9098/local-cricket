<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\StatsCalculatorService;
use App\Services\MatchService;
use App\Models\CricketMatch;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MatchStatsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test basic stats calculator helpers.
     */
    public function test_stats_calculator_helpers()
    {
        // 1. Strike Rate
        $sr = StatsCalculatorService::calculateStrikeRate(50, 25);
        $this->assertEquals(200.0, $sr);

        $srZero = StatsCalculatorService::calculateStrikeRate(0, 0);
        $this->assertEquals(0.0, $srZero);

        // 2. Overs to Balls
        $balls = StatsCalculatorService::oversToBalls(3.2); // 3 overs + 2 balls = 20 balls
        $this->assertEquals(20, $balls);

        // 3. Balls to Overs
        $overs = StatsCalculatorService::ballsToOvers(20);
        $this->assertEquals(3.2, $overs);

        // 4. Economy Rate
        $econ = StatsCalculatorService::calculateEconomy(20, 3.2); // 20 runs in 20 balls = 6.00 econ
        $this->assertEquals(6.0, $econ);
    }

    /**
     * Test match stats calculation and API endpoint.
     */
    public function test_match_stats_api_endpoint()
    {
        // Create Team 1 and Team 2
        $team1 = Team::create(['name' => 'India', 'short_name' => 'IND']);
        $team2 = Team::create(['name' => 'Australia', 'short_name' => 'AUS']);

        // Create a live match
        $match = CricketMatch::create([
            'team1_id' => $team1->id,
            'team2_id' => $team2->id,
            'match_type' => 'T20',
            'status' => 'live',
            'current_innings' => 1,
            'team1_score' => 100,
            'team1_wickets' => 2,
            'team1_overs' => 10.0,
            'team2_score' => 0,
            'team2_wickets' => 0,
            'team2_overs' => 0.0,
        ]);

        $matchService = new MatchService();
        $stats = $matchService->getCalculatedStats($match);

        // Assert CRR is 10.0 (100 runs in 10 overs)
        $this->assertEquals(10.0, $stats['crr']);
        $this->assertArrayHasKey('win_probability', $stats);
        $this->assertArrayHasKey('team1', $stats['win_probability']);
        $this->assertArrayHasKey('team2', $stats['win_probability']);

        // Request stats API endpoint
        $response = $this->getJson("/api/matches/{$match->id}/stats");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'crr',
                    'rrr',
                    'win_probability' => ['team1', 'team2', 'draw'],
                    'partnership' => ['runs', 'balls', 'batsmen'],
                    'overs_left'
                ]
            ]);
    }
}
