<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders_properly()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Welcome Back');
        $response->assertSee('Forgot Password?');
        $response->assertSee('auth-card');
        $response->assertSee('auth-page-wrapper');
    }

    public function test_register_page_renders_properly()
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
        $response->assertSee('Create Account');
        $response->assertSee('auth-card');
        $response->assertSee('auth-page-wrapper');
    }

    public function test_forgot_password_page_renders_properly()
    {
        $response = $this->get('/forgot-password');
        $response->assertStatus(200);
        $response->assertSee('Forgot Password');
        $response->assertSee('Send Reset Link');
        $response->assertSee('auth-card');
    }

    public function test_forgot_password_generates_and_stores_db_token()
    {
        $email = 'unit_test_user_' . Str::random(5) . '@example.com';
        $user = User::create([
            'name' => 'Unit Test User',
            'email' => $email,
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        $response = $this->post('/forgot-password', [
            'email' => $email,
        ]);

        $response->assertSessionHas('status');
        $response->assertSessionHas('reset_token');
        $response->assertSessionHas('reset_url');

        // Verify token in database
        $tokenRecord = DB::table('password_reset_tokens')->where('email', $email)->first();
        $this->assertNotNull($tokenRecord);
        $this->assertEquals($email, $tokenRecord->email);
        $this->assertNotEmpty($tokenRecord->token);
        $this->assertEquals('PENDING', $tokenRecord->status);
        $this->assertNull($tokenRecord->used_at);

        // Clean up
        DB::table('password_reset_tokens')->where('email', $email)->delete();
        $user->delete();
    }

    public function test_reset_password_updates_user_password_and_retains_token_as_completed()
    {
        $email = 'unit_test_reset_' . Str::random(5) . '@example.com';
        $user = User::create([
            'name' => 'Unit Test Reset User',
            'email' => $email,
            'password' => Hash::make('initialpassword123'),
            'role' => 'user',
        ]);

        $token = Str::random(64);
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            ['token' => $token, 'status' => 'PENDING', 'created_at' => now()]
        );

        $response = $this->post('/reset-password', [
            'email' => $email,
            'token' => $token,
            'password' => 'brandnewpassword123',
            'password_confirmation' => 'brandnewpassword123',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHas('success');

        // Verify user password changed
        $updatedUser = User::where('email', $email)->first();
        $this->assertTrue(Hash::check('brandnewpassword123', $updatedUser->password));

        // Verify token is retained in database and marked as COMPLETED
        $tokenRecord = DB::table('password_reset_tokens')->where('email', $email)->first();
        $this->assertNotNull($tokenRecord);
        $this->assertEquals('COMPLETED', $tokenRecord->status);
        $this->assertNotNull($tokenRecord->used_at);

        // Clean up
        DB::table('password_reset_tokens')->where('email', $email)->delete();
        $user->delete();
    }

    public function test_user_login_redirects_to_home_page()
    {
        $user = User::create([
            'name' => 'Home Redir User',
            'email' => 'redir_user_' . Str::random(5) . '@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($user);

        $user->delete();
    }

    public function test_user_cannot_edit_or_manage_other_users_tournament()
    {
        $owner = User::create([
            'name' => 'Owner User',
            'email' => 'owner_' . Str::random(5) . '@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        $attacker = User::create([
            'name' => 'Other User',
            'email' => 'attacker_' . Str::random(5) . '@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        $tournament = \App\Models\Tournament::create([
            'user_id' => $owner->id,
            'name' => 'Owner Tournament',
            'short_name' => 'OWNER',
            'format' => 'T20',
            'category' => 'local',
            'status' => 'draft',
            'views_count' => 0,
        ]);

        // 1. Attacker tries to manage owner's tournament -> 403 Forbidden
        $response = $this->actingAs($attacker)->get("/local/tournament/{$tournament->id}/manage");
        $response->assertStatus(403);

        // 2. Attacker tries to add a team to owner's tournament -> 403 Forbidden
        $response = $this->actingAs($attacker)->post("/local/tournament/{$tournament->id}/add-team", [
            'name' => 'Hacked Team'
        ]);
        $response->assertStatus(403);

        // 3. Attacker CAN view owner's tournament preview -> 200 OK
        $response = $this->actingAs($attacker)->get("/local/tournament/{$tournament->id}/preview");
        $response->assertStatus(200);
        $response->assertSee('Owner Tournament');

        // 4. Owner CAN manage own tournament -> 200 OK
        $response = $this->actingAs($owner)->get("/local/tournament/{$tournament->id}/manage");
        $response->assertStatus(200);
        $response->assertSee('Owner Tournament');

        $tournament->delete();
        $owner->delete();
        $attacker->delete();
    }
}

