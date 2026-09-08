@extends('layouts.app')

@section('content')
<main class="auth-page-wrapper">
    <div class="auth-card">
        
        <div class="auth-header">
            <a href="{{ route('home') }}" style="display: inline-block;">
                <img src="{{ asset('images/logo.png') }}" alt="CricketKaScore" class="auth-logo">
            </a>
            <h1 class="auth-title">Forgot Password</h1>
            <p class="auth-subtitle">Enter your registered email to reset your account password</p>
        </div>

        @if(session('status'))
            <div class="auth-alert-success">
                <div style="font-weight: 800; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    <span>Token Generated &amp; Saved in Database!</span>
                </div>
                <p style="margin: 0 0 10px 0; font-size: 0.82rem;">{{ session('status') }}</p>
                
                @if(session('reset_url'))
                    <div style="margin-top: 10px; padding: 12px; background: rgba(0,0,0,0.15); border-radius: 8px; border: 1px solid rgba(34, 197, 94, 0.3);">
                        <div style="font-size: 0.76rem; font-weight: 700; text-transform: uppercase; margin-bottom: 6px; color: #4ade80;">Reset Password Action:</div>
                        <a href="{{ session('reset_url') }}" style="display: block; padding: 9px 14px; background: #22c55e; color: #ffffff; font-weight: 800; font-size: 0.84rem; text-align: center; border-radius: 6px; text-decoration: none; box-shadow: 0 4px 12px rgba(34, 197, 94, 0.35);">
                            Reset Password Now &rarr;
                        </a>
                    </div>
                @endif
            </div>
        @endif

        @if(isset($errors) && $errors->any())
            <div class="auth-alert-danger">
                @foreach ($errors->all() as $error)
                    <p style="margin: 0;">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="auth-form">
            @csrf
            <div class="auth-input-group">
                <label class="auth-label">Registered Email Address *</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="name@example.com" class="auth-input">
            </div>

            <button type="submit" class="auth-btn-primary">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                <span>Send Reset Link</span>
            </button>
        </form>

        <p class="auth-footer-text">
            Remember your password? 
            <a href="{{ route('login') }}">Back to Sign in</a>
        </p>

    </div>
</main>
@endsection
