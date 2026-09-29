@extends('layouts.app')

@php
    $pageTitle = 'Verify Email Address — Enter OTP Code | CricketKaScore';
    $metaDesc = 'Enter the 6-digit verification code sent to your email to verify and activate your CricketKaScore account.';
    $metaKeywords = 'verify email, OTP verification, CricketKaScore account verification';
    $canonicalUrl = route('verification.notice');
@endphp

@section('pageTitle', $pageTitle)
@section('meta_description', $metaDesc)
@section('meta_keywords', $metaKeywords)
@section('meta_robots', 'noindex, nofollow')

@section('content')
<main class="auth-page-wrapper" style="min-height: 80vh; display: flex; align-items: center; justify-content: center; padding: 40px 16px;">
    <div class="auth-card" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 18px; max-width: 480px; width: 100%; padding: 36px 28px; box-shadow: 0 20px 40px rgba(0,0,0,0.5); text-align: center;">
        
        <!-- Header Icon & Title -->
        <div style="margin-bottom: 24px;">
            <div style="width: 64px; height: 64px; margin: 0 auto 16px; border-radius: 50%; background: rgba(56, 189, 248, 0.12); border: 1.5px solid #38bdf8; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; color: #38bdf8;">
                ✉️
            </div>
            <h1 style="font-size: 1.6rem; font-weight: 900; color: var(--text-main); margin: 0 0 6px 0; letter-spacing: -0.02em;">
                Verify Your Email
            </h1>
            <p style="color: var(--text-muted); font-size: 0.9rem; line-height: 1.5; margin: 0;">
                We have sent a 6-digit verification code to<br>
                <strong style="color: #38bdf8; word-break: break-all;">{{ $unverifiedUser->email ?? 'your email address' }}</strong>
            </p>
        </div>

        <!-- Flash Alerts -->
        @if(session('success'))
            <div style="background: rgba(56, 189, 248, 0.12); border: 1px solid rgba(56, 189, 248, 0.4); color: #bae6fd; border-radius: 10px; padding: 12px; margin-bottom: 20px; font-size: 0.88rem; font-weight: 600;">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error') || (isset($errors) && $errors->any()))
            <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #fca5a5; border-radius: 10px; padding: 12px; margin-bottom: 20px; font-size: 0.88rem; font-weight: 600;">
                {{ session('error') ?? $errors->first() }}
            </div>
        @endif

        <!-- OTP Form -->
        <form method="POST" action="{{ route('verification.verify') }}" id="otpForm" onsubmit="return handleOtpSubmit(event)">
            @csrf
            
            <input type="hidden" name="otp" id="fullOtpInput">

            <!-- 6-Boxes Digit Inputs -->
            <div style="display: flex; justify-content: center; gap: 8px; margin-bottom: 12px;" id="otpBoxesContainer">
                <input type="text" class="otp-box-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autofocus required>
                <input type="text" class="otp-box-input" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                <input type="text" class="otp-box-input" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                <input type="text" class="otp-box-input" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                <input type="text" class="otp-box-input" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                <input type="text" class="otp-box-input" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
            </div>

            <div style="font-size: 0.8rem; color: #f59e0b; margin-bottom: 20px; font-weight: 600;">
                ⏱️ Verification code is valid for 2 minutes.
            </div>

            <button type="submit" id="submitOtpBtn" style="width: 100%; padding: 13px 20px; background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: white; border: none; border-radius: 10px; font-weight: 800; font-size: 0.95rem; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 14px rgba(2, 132, 199, 0.35);">
                Verify &amp; Activate Account
            </button>
        </form>

        <!-- Resend OTP & Links -->
        <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--border-color); display: flex; flex-direction: column; gap: 12px; font-size: 0.85rem;">
            <div style="color: var(--text-muted);">
                Didn't receive the email code?
            </div>
            
            <form method="POST" action="{{ route('verification.resend') }}" id="resendForm" style="margin: 0;">
                @csrf
                <button type="submit" id="resendBtn" style="background: none; border: none; color: #38bdf8; font-weight: 700; font-size: 0.88rem; cursor: pointer; padding: 0; text-decoration: underline;">
                    Resend Code
                </button>
                <span id="resendTimer" style="display: none; color: var(--text-dim); font-size: 0.82rem; font-weight: 600;">
                    Resend in <strong id="timerSeconds" style="color: #38bdf8;">60</strong>s
                </span>
            </form>

            <div style="margin-top: 6px;">
                <a href="{{ route('register') }}" style="color: var(--text-dim); text-decoration: none; font-size: 0.82rem;">
                    &larr; Use a different email address
                </a>
            </div>
        </div>

    </div>
</main>

<style>
.otp-box-input {
    width: 48px;
    height: 54px;
    background: var(--bg-card-secondary);
    border: 2px solid var(--border-color);
    border-radius: 10px;
    color: var(--text-main);
    font-size: 1.5rem;
    font-weight: 900;
    text-align: center;
    outline: none;
    transition: all 0.2s;
}
.otp-box-input:focus {
    border-color: #38bdf8;
    box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.25);
    background: rgba(56, 189, 248, 0.05);
}
@media (max-width: 480px) {
    .otp-box-input {
        width: 40px;
        height: 48px;
        font-size: 1.3rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const inputs = document.querySelectorAll('.otp-box-input');
    const fullOtpInput = document.getElementById('fullOtpInput');
    const resendBtn = document.getElementById('resendBtn');
    const resendTimer = document.getElementById('resendTimer');
    const timerSeconds = document.getElementById('timerSeconds');

    // Auto-advance & Backspace handling
    inputs.forEach((input, index) => {
        input.addEventListener('input', (e) => {
            const val = e.target.value.replace(/[^0-9]/g, '');
            e.target.value = val ? val[0] : '';
            if (val && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
            updateFullOtp();
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !input.value && index > 0) {
                inputs[index - 1].focus();
            }
        });

        // Paste support (e.g. user copies 6-digit code)
        input.addEventListener('paste', (e) => {
            e.preventDefault();
            const pasteData = (e.clipboardData || window.clipboardData).getData('text').trim().replace(/[^0-9]/g, '');
            if (pasteData.length > 0) {
                for (let i = 0; i < inputs.length; i++) {
                    if (i < pasteData.length) {
                        inputs[i].value = pasteData[i];
                    }
                }
                const nextFocus = Math.min(pasteData.length, inputs.length - 1);
                inputs[nextFocus].focus();
                updateFullOtp();
            }
        });
    });

    function updateFullOtp() {
        let code = '';
        inputs.forEach(i => code += i.value);
        fullOtpInput.value = code;
    }

    window.handleOtpSubmit = function(e) {
        updateFullOtp();
        if (fullOtpInput.value.length < 6) {
            e.preventDefault();
            alert('Please enter all 6 digits of your verification code.');
            return false;
        }
        return true;
    };

    // Resend countdown timer (60s)
    let countdown = 60;
    const isResent = sessionStorage.getItem('otp_timer_active');
    
    function startCountdown() {
        resendBtn.style.display = 'none';
        resendTimer.style.display = 'inline-block';
        const timer = setInterval(() => {
            countdown--;
            timerSeconds.innerText = countdown;
            if (countdown <= 0) {
                clearInterval(timer);
                resendBtn.style.display = 'inline-block';
                resendTimer.style.display = 'none';
                sessionStorage.removeItem('otp_timer_active');
            }
        }, 1000);
    }

    document.getElementById('resendForm').addEventListener('submit', () => {
        sessionStorage.setItem('otp_timer_active', 'true');
    });

    if (isResent) {
        startCountdown();
    }
});
</script>
@endsection
