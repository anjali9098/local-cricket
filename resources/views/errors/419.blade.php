<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Expired — CricketKaScore</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: #0b0f17;
            color: #f8fafc;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .error-card {
            background: #161b22;
            border: 1px solid #30363d;
            border-radius: 16px;
            padding: 40px 32px;
            max-width: 480px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0,0,0,0.6);
        }
        .icon {
            font-size: 3.5rem;
            margin-bottom: 16px;
            display: inline-block;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.08); }
        }
        h1 {
            color: #f8fafc;
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 10px;
        }
        p {
            color: #94a3b8;
            font-size: 0.95rem;
            line-height: 1.5;
            margin-bottom: 28px;
        }
        .actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn-primary {
            background: #0284c7;
            color: white;
            padding: 12px 24px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.95rem;
            border: none;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-primary:hover { background: #0369a1; }
        .btn-secondary {
            background: #21262d;
            color: #cbd5e1;
            padding: 12px 24px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            border: 1px solid #30363d;
            transition: all 0.2s;
        }
        .btn-secondary:hover { background: #30363d; color: white; }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="icon">⏱️</div>
        <h1>Session Expired</h1>
        <p>Your session timed out due to inactivity. Please refresh the page to continue right where you left off.</p>
        <div class="actions">
            <button onclick="window.location.reload();" class="btn-primary">↻ Refresh Page</button>
            <a href="{{ url('/') }}" class="btn-secondary">Go to Home</a>
        </div>
    </div>
</body>
</html>
