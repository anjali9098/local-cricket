@extends('layouts.app')

@section('content')
<main class="auth-page-wrapper">
    <div class="auth-card">
        
        <div class="auth-header">
            <a href="{{ route('home') }}" style="display: inline-block;">
                <img src="{{ asset('images/logo.png') }}" alt="CricketKaScore" class="auth-logo">
            </a>
            <h1 class="auth-title">Reset Password</h1>
            <p class="auth-subtitle">Create a new secure password for your account</p>
        </div>

        @if(isset($errors) && $errors->any())
            <div class="auth-alert-danger">
                @foreach ($errors->all() as $error)
                    <p style="margin: 0;">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" class="auth-form">
            @csrf
            
            <input type="hidden" name="token" value="{{ $token ?? old('token') }}">

            <div class="auth-input-group">
                <label class="auth-label">Email Address *</label>
                <input type="email" name="email" value="{{ $email ?? old('email') }}" required autofocus placeholder="name@example.com" class="auth-input">
            </div>

            <div class="auth-input-group">
                <label class="auth-label">New Password * (Min. 6 characters)</label>
                <div class="password-input-wrapper">
                    <input type="password" id="reset-password" name="password" minlength="6" required placeholder="Enter new password" class="auth-input">
                    <button type="button" class="password-toggle-btn" data-target="reset-password" title="Show password" aria-label="Toggle password visibility">
                        <svg class="eye-icon eye-show" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <svg class="eye-icon eye-hide" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                            <line x1="1" y1="1" x2="23" y2="23"></line>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="auth-input-group">
                <label class="auth-label">Confirm New Password *</label>
                <div class="password-input-wrapper">
                    <input type="password" id="reset-password-confirm" name="password_confirmation" minlength="6" required placeholder="Re-enter new password" class="auth-input">
                    <button type="button" class="password-toggle-btn" data-target="reset-password-confirm" title="Show password" aria-label="Toggle password visibility">
                        <svg class="eye-icon eye-show" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <svg class="eye-icon eye-hide" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                            <line x1="1" y1="1" x2="23" y2="23"></line>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="auth-btn-primary">
                Update Password &amp; Sign In
            </button>
        </form>

        <p class="auth-footer-text">
            Never mind? 
            <a href="{{ route('login') }}">Back to Sign in</a>
        </p>

    </div>
</main>
@endsection
