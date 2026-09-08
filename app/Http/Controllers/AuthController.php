<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\User;

class AuthController extends Controller
{
    private function redirectBasedOnRole()
    {
        if (Auth::user()->role === 'superadmin') {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('home');
    }

    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole();
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return $this->redirectBasedOnRole();
        }

        return back()->withErrors([
            'email' => 'Invalid email or password.',
        ])->onlyInput('email');
    }

    public function showGoogleLoginSim()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole();
        }
        return view('auth.google_sim');
    }

    public function googleLoginSimPost(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'name' => ['required', 'string', 'max:255']
        ]);

        $email = trim($request->input('email'));
        $name = trim($request->input('name'));

        $role = 'user';
        if ($email === 'anjalimalviya0804@gmail.com' || $email === 'admin@cricketkascore.com') {
            $role = 'superadmin';
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make('google123'),
                'role' => $role
            ]);
        } else {
            if ($role === 'superadmin' && $user->role !== 'superadmin') {
                $user->role = 'superadmin';
                $user->save();
            }
        }

        Auth::login($user);
        return $this->redirectBasedOnRole()->with('success', 'Logged in via Google successfully!');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole();
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'user'
        ]);

        Auth::login($user);
        return $this->redirectBasedOnRole()->with('success', 'Account created successfully!');
    }

    public function showForgotPassword()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole();
        }
        return view('auth.forgot_password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ], [
            'email.exists' => 'We could not find an account associated with this email address.',
        ]);

        $email = trim($request->input('email'));
        $token = Str::random(64);

        // Save / Update token in password_reset_tokens table
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => $token,
                'status' => 'PENDING',
                'created_at' => Carbon::now(),
                'used_at' => null,
            ]
        );

        $resetUrl = route('password.reset', ['token' => $token, 'email' => $email]);

        return back()->with([
            'status' => 'Password reset token has been generated and securely saved to the database.',
            'reset_url' => $resetUrl,
            'reset_token' => $token,
            'reset_email' => $email
        ]);
    }

    public function showResetPassword(Request $request, $token = null)
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole();
        }

        $token = $token ?? $request->query('token');
        $email = $request->query('email');

        return view('auth.reset_password', compact('token', 'email'));
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'exists:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'email.exists' => 'We could not find an account associated with this email address.',
            'password.confirmed' => 'The password confirmation does not match.',
            'password.min' => 'Password must be at least 6 characters.',
        ]);

        $email = trim($request->input('email'));
        $token = trim($request->input('token'));

        $record = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (!$record || $record->token !== $token) {
            return back()->withErrors(['token' => 'Invalid password reset token. Please request a new reset link.'])->withInput();
        }

        // Check if token has already been used
        if (isset($record->status) && $record->status === 'COMPLETED') {
            return back()->withErrors(['token' => 'This password reset link has already been used. Please request a new reset link if needed.'])->withInput();
        }

        // Check if token expired (60 minutes)
        if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $email)->update([
                'status' => 'EXPIRED',
            ]);
            return back()->withErrors(['token' => 'This password reset token has expired. Please request a new link.'])->withInput();
        }

        $user = User::where('email', $email)->first();
        if ($user) {
            $user->password = Hash::make($request->input('password'));
            $user->save();
        }

        // Keep the token and email permanently in database with COMPLETED status and timestamp
        DB::table('password_reset_tokens')->where('email', $email)->update([
            'status' => 'COMPLETED',
            'used_at' => Carbon::now(),
        ]);

        return redirect()->route('login')->with('success', 'Your password has been reset successfully! Please sign in with your new password.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }
}

