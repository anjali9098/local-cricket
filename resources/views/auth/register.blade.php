@extends('layouts.app')

@section('content')
<main class="auth-page-wrapper">
    <div class="auth-card">
        
        <div class="auth-header">
            <a href="{{ route('home') }}" style="display: inline-block;">
                <img src="{{ asset('images/logo.png') }}" alt="CricketKaScore" class="auth-logo">
            </a>
            <h1 class="auth-title">Create Account</h1>
            <p class="auth-subtitle">Join CricketKaScore to track live scores & local cricket</p>
        </div>

        @if(isset($errors) && $errors->any())
            <div class="auth-alert-danger">
                @foreach ($errors->all() as $error)
                    <p style="margin: 0;">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <!-- Google OAuth Button -->
        <a href="{{ route('google.login') }}" class="auth-btn-google">
            <svg width="18" height="18" viewBox="0 0 24 24"><path fill="#EA4335" d="M12 5c1.6 0 3 .6 4.1 1.7l3.1-3.1C17.3 1.8 14.8 1 12 1 7.5 1 3.7 3.6 1.9 7.3l3.7 2.9C6.5 7.4 9 5 12 5z"/><path fill="#4285F4" d="M23.5 12.3c0-.8-.1-1.6-.2-2.3H12v4.6h6.5c-.3 1.5-1.1 2.8-2.4 3.7l3.7 2.9c2.2-2 3.7-5 3.7-8.9z"/><path fill="#FBBC05" d="M5.6 14.8c-.2-.7-.4-1.5-.4-2.3s.2-1.6.4-2.3L1.9 7.3C.7 9.7 0 12.3 0 15.1s.7 5.4 1.9 7.8l3.7-2.9z"/><path fill="#34A853" d="M12 23.5c3.2 0 6-1.1 8-3l-3.7-2.9c-1.1.7-2.5 1.2-4.3 1.2-3 0-5.5-2.4-6.4-5.2L1.9 16.5C3.7 20.2 7.5 23.5 12 23.5z"/></svg>
            <span>Continue with Google</span>
        </a>

        <div class="auth-divider">
            <div class="auth-divider-line"></div>
            <span>Or with email</span>
            <div class="auth-divider-line"></div>
        </div>

        <form method="POST" action="{{ route('register.post') }}" class="auth-form">
            @csrf
            <div class="auth-input-group">
                <label class="auth-label">Your Name *</label>
                <input type="text" name="name" value="{{ old('name') }}" required autofocus placeholder="John Doe" class="auth-input">
            </div>

            <div class="auth-input-group">
                <label class="auth-label">Email Address *</label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="name@example.com" class="auth-input">
            </div>

            <div class="auth-input-group">
                <label class="auth-label">Password * (Min. 6 characters)</label>
                <div class="password-input-wrapper">
                    <input type="password" id="reg-password" name="password" minlength="6" required placeholder="Create a strong password" class="auth-input">
                    <button type="button" class="password-toggle-btn" data-target="reg-password" title="Show password" aria-label="Toggle password visibility">
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
                <label class="auth-label">Confirm Password *</label>
                <div class="password-input-wrapper">
                    <input type="password" id="reg-password-confirm" name="password_confirmation" minlength="6" required placeholder="Re-enter your password" class="auth-input">
                    <button type="button" class="password-toggle-btn" data-target="reg-password-confirm" title="Show password" aria-label="Toggle password visibility">
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
                Create Account
            </button>
        </form>

        <p class="auth-footer-text">
            Already have an account? 
            <a href="{{ route('login') }}">Sign in</a>
        </p>
    </div>
</main>
@endsection
