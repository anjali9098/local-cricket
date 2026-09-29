<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\User;
use App\Models\OtpVerification;
use App\Services\RecaptchaService;
use App\Mail\SendOtpMail;

class AuthController extends Controller
{
    private function redirectBasedOnRole()
    {
        if (Auth::check() && Auth::user()->role === 'superadmin') {
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
        try {
            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required'],
            ], [
                'email.required' => 'Please enter your email address.',
                'email.email' => 'Please enter a valid email address.',
                'password.required' => 'Please enter your password.',
            ]);

            // Verify Google reCAPTCHA if token is submitted
            if ($request->has('g-recaptcha-response')) {
                if (!RecaptchaService::verify($request->input('g-recaptcha-response'), $request->ip())) {
                    return back()->withErrors([
                        'recaptcha' => 'Google reCAPTCHA verification failed. Please check the "I am not a robot" box.',
                    ])->onlyInput('email');
                }
            }

            $user = User::where('email', strtolower(trim($credentials['email'])))->first();

            if (!$user) {
                return back()->withErrors([
                    'email' => 'No account found with this email. Please create an account.',
                ])->onlyInput('email');
            }

            if (!Hash::check($credentials['password'], $user->password)) {
                return back()->withErrors([
                    'email' => 'Incorrect password. Please try again or use Forgot Password.',
                ])->onlyInput('email');
            }

            // Check if user's email is verified
            if (!$user->isEmailVerified()) {
                $otp = sprintf("%06d", mt_rand(100000, 999999));
                $user->verification_otp = $otp;
                $user->otp_expires_at = Carbon::now()->addMinutes(2);
                $user->save();

                // Record OTP generation into otp_verifications database table
                OtpVerification::create([
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'otp' => $otp,
                    'status' => 'PENDING',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'expires_at' => Carbon::now()->addMinutes(2),
                ]);

                try {
                    Mail::to($user->email)->send(new SendOtpMail($user, $otp));
                } catch (\Throwable $me) {
                    Log::warning('OTP mail delivery error on login: ' . $me->getMessage());
                }

                session(['unverified_user_id' => $user->id]);

                return redirect()->route('verification.notice')->with('error', 'Your email is not verified yet. We have sent a 6-digit verification code to your email.');
            }

            Auth::login($user);
            $request->session()->regenerate();
            return $this->redirectBasedOnRole();
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return back()->withErrors($ve->errors())->onlyInput('email');
        } catch (\Throwable $e) {
            return back()->withErrors(['email' => 'Login failed: ' . $e->getMessage()])->onlyInput('email');
        }
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
        try {
            $request->validate([
                'email' => ['required', 'email'],
                'name' => ['required', 'string', 'max:255']
            ], [
                'email.required' => 'Please enter your email address.',
                'email.email' => 'Please enter a valid email address.',
                'name.required' => 'Please enter your name.',
            ]);

            $email = strtolower(trim($request->input('email')));
            $name = trim($request->input('name'));

            $user = User::where('email', $email)->first();
            if (!$user) {
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make(Str::random(16)),
                    'role' => 'user',
                    'email_verified_at' => Carbon::now() // Google accounts are pre-verified
                ]);
            } else {
                if (!$user->email_verified_at) {
                    $user->email_verified_at = Carbon::now();
                    $user->save();
                }
            }

            Auth::login($user);
            return $this->redirectBasedOnRole()->with('success', 'Logged in via Google successfully!');
        } catch (\Throwable $e) {
            return back()->withErrors(['email' => 'Google Login failed: ' . $e->getMessage()]);
        }
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
        try {
            $data = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'string', 'min:6', 'confirmed'],
            ], [
                'name.required' => 'Please enter your full name.',
                'email.required' => 'Please enter your email address.',
                'email.email' => 'Please enter a valid email address.',
                'email.unique' => 'This email address is already registered. Please sign in instead.',
                'password.required' => 'Please create a password.',
                'password.min' => 'Password must be at least 6 characters.',
                'password.confirmed' => 'The password confirmation does not match.',
            ]);

            // Verify Google reCAPTCHA
            if ($request->has('g-recaptcha-response')) {
                if (!RecaptchaService::verify($request->input('g-recaptcha-response'), $request->ip())) {
                    return back()->withErrors([
                        'recaptcha' => 'Google reCAPTCHA verification failed. Please check the "I am not a robot" box.',
                    ])->onlyInput('name', 'email');
                }
            }

            $email = strtolower(trim($data['email']));

            // Generate 6-Digit Secure OTP
            $otp = sprintf("%06d", mt_rand(100000, 999999));

            // Create new user with default 'user' role and unverified email
            $user = User::create([
                'name' => trim($data['name']),
                'email' => $email,
                'password' => Hash::make($data['password']),
                'role' => 'user',
                'email_verified_at' => null,
                'verification_otp' => $otp,
                'otp_expires_at' => Carbon::now()->addMinutes(2),
            ]);

            // Save OTP Record to database (phpMyAdmin otp_verifications table)
            OtpVerification::create([
                'user_id' => $user->id,
                'email' => $email,
                'otp' => $otp,
                'status' => 'PENDING',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'expires_at' => Carbon::now()->addMinutes(2),
            ]);

            // Send OTP verification email to user
            try {
                Mail::to($user->email)->send(new SendOtpMail($user, $otp));
            } catch (\Throwable $me) {
                Log::warning('OTP email dispatch failed: ' . $me->getMessage());
            }

            // Store session for OTP verification
            session([
                'unverified_user_id' => $user->id
            ]);

            return redirect()->route('verification.notice')->with('success', 'Account created! A 6-digit verification code has been sent to your email.');
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return back()->withErrors($ve->errors())->onlyInput('name', 'email');
        } catch (\Throwable $e) {
            return back()->withErrors(['email' => 'Registration error: ' . $e->getMessage()])->onlyInput('name', 'email');
        }
    }

    public function showVerifyOtp()
    {
        $userId = session('unverified_user_id');
        $unverifiedUser = null;

        if ($userId) {
            $unverifiedUser = User::find($userId);
        } elseif (Auth::check() && !Auth::user()->isEmailVerified()) {
            $unverifiedUser = Auth::user();
        }

        if (!$unverifiedUser || $unverifiedUser->isEmailVerified()) {
            return $this->redirectBasedOnRole();
        }

        return view('auth.verify_otp', compact('unverifiedUser'));
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ], [
            'otp.required' => 'Please enter the 6-digit verification code.',
            'otp.size' => 'The verification code must be exactly 6 digits.',
        ]);

        $userId = session('unverified_user_id');
        $user = null;

        if ($userId) {
            $user = User::find($userId);
        } elseif (Auth::check() && !Auth::user()->isEmailVerified()) {
            $user = Auth::user();
        }

        if (!$user) {
            return redirect()->route('login')->with('error', 'Session expired. Please sign in to verify your account.');
        }

        $inputOtp = trim($request->input('otp'));

        // Retrieve latest OTP record in otp_verifications table
        $otpRecord = OtpVerification::where('email', $user->email)
            ->where('status', 'PENDING')
            ->latest()
            ->first();

        // 1. Check if OTP is incorrect
        if ($user->verification_otp !== $inputOtp) {
            if ($otpRecord) {
                $otpRecord->entered_otp = $inputOtp;
                $otpRecord->status = 'FAILED';
                $otpRecord->save();
            } else {
                OtpVerification::create([
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'otp' => $user->verification_otp ?? '',
                    'entered_otp' => $inputOtp,
                    'status' => 'FAILED',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            }
            return back()->with('error', 'Invalid verification code. Please check your email and try again.');
        }

        // 2. Check if OTP has expired (2 minutes)
        if ($user->otp_expires_at && Carbon::parse($user->otp_expires_at)->isPast()) {
            if ($otpRecord) {
                $otpRecord->entered_otp = $inputOtp;
                $otpRecord->status = 'EXPIRED';
                $otpRecord->save();
            }
            return back()->with('error', 'The verification code has expired (valid for 2 minutes). Please click "Resend Code" to get a fresh OTP.');
        }

        // 3. OTP is valid! Update otp_verifications log in database
        if ($otpRecord) {
            $otpRecord->entered_otp = $inputOtp;
            $otpRecord->status = 'VERIFIED';
            $otpRecord->verified_at = Carbon::now();
            $otpRecord->save();
        } else {
            OtpVerification::create([
                'user_id' => $user->id,
                'email' => $user->email,
                'otp' => $inputOtp,
                'entered_otp' => $inputOtp,
                'status' => 'VERIFIED',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'expires_at' => $user->otp_expires_at,
                'verified_at' => Carbon::now(),
            ]);
        }

        // Mark user email as verified in users database table
        $user->email_verified_at = Carbon::now();
        $user->save();

        Auth::login($user);
        session()->forget(['unverified_user_id']);
        $request->session()->regenerate();

        return $this->redirectBasedOnRole()->with('success', 'Email verified successfully! Welcome to CricketKaScore.');
    }

    public function resendOtp(Request $request)
    {
        $userId = session('unverified_user_id');
        $user = null;

        if ($userId) {
            $user = User::find($userId);
        } elseif (Auth::check() && !Auth::user()->isEmailVerified()) {
            $user = Auth::user();
        }

        if (!$user) {
            return redirect()->route('login')->with('error', 'Session expired. Please sign in again.');
        }

        $otp = sprintf("%06d", mt_rand(100000, 999999));
        $user->verification_otp = $otp;
        $user->otp_expires_at = Carbon::now()->addMinutes(2);
        $user->save();

        // Record new OTP in otp_verifications database table
        OtpVerification::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'otp' => $otp,
            'status' => 'PENDING',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'expires_at' => Carbon::now()->addMinutes(2),
        ]);

        try {
            Mail::to($user->email)->send(new SendOtpMail($user, $otp));
        } catch (\Throwable $me) {
            Log::warning('OTP resend email error: ' . $me->getMessage());
        }

        return back()->with('success', 'A fresh 6-digit verification code has been sent to your email!');
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
