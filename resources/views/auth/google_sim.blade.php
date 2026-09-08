@extends('layouts.app')

@section('content')
<main class="auth-page-wrapper">
    <div class="auth-card" style="display: flex; flex-direction: column; align-items: center;">
        
        <!-- Google Logo Mock -->
        <div style="font-size: 2.2rem; font-weight: 800; letter-spacing: -0.04em; margin-bottom: 12px; font-family: 'Product Sans', 'Google Sans', sans-serif;">
            <span style="color: #4285F4;">G</span><span style="color: #EA4335;">o</span><span style="color: #FBBC05;">o</span><span style="color: #4285F4;">g</span><span style="color: #34A853;">l</span><span style="color: #EA4335;">e</span>
        </div>

        <h1 class="auth-title" style="text-align: center;">Sign in with Google</h1>
        <p class="auth-subtitle" style="text-align: center; margin-bottom: 24px;">to continue to CricketKaScore</p>

        @if(isset($errors) && $errors->any())
            <div class="auth-alert-danger" style="width: 100%;">
                @foreach ($errors->all() as $error)
                    <p style="margin: 0;">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('google.login.post') }}" class="auth-form">
            @csrf
            
            <div class="auth-input-group">
                <label class="auth-label">Email Address *</label>
                <input type="email" name="email" required autofocus placeholder="Enter your Google email" class="auth-input">
            </div>

            <div class="auth-input-group">
                <label class="auth-label">Your Full Name *</label>
                <input type="text" name="name" required placeholder="Enter your display name" class="auth-input">
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
                <a href="{{ route('login') }}" class="auth-link-forgot" style="font-size: 0.85rem;">Cancel</a>
                <button type="submit" class="auth-btn-primary" style="width: auto; padding: 10px 24px; margin-top: 0;">
                    Continue &rarr;
                </button>
            </div>
        </form>
        
        <div class="auth-info-box" style="width: 100%; text-align: center; font-size: 0.76rem; margin-top: 24px;">
            By signing in, Google will verify your identity to securely sign you in or create your CricketKaScore account.
        </div>

    </div>
</main>
@endsection
