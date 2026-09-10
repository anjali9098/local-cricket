<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Page Not Found | CricketKaScore</title>
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
        .code {
            font-size: 3.5rem;
            font-weight: 900;
            color: #38bdf8;
            margin-bottom: 8px;
            letter-spacing: -1px;
        }
        h1 {
            color: #f8fafc;
            font-size: 1.4rem;
            font-weight: 800;
            margin-bottom: 10px;
        }
        p {
            color: #94a3b8;
            font-size: 0.95rem;
            line-height: 1.5;
            margin-bottom: 28px;
        }
        .btn-primary {
            background: #0284c7;
            color: white;
            padding: 12px 24px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.95rem;
            display: inline-block;
            transition: background 0.2s;
        }
        .btn-primary:hover { background: #0369a1; }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="code">404</div>
        <h1>Page Not Found</h1>
        <p>The page or match you are looking for does not exist or may have been moved.</p>
        <a href="{{ url('/') }}" class="btn-primary">&larr; Back to Home</a>
    </div>
</body>
</html>
