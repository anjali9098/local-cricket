<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 — Server Error Diagnostic</title>
    <style>
        body { background: #0b0f17; color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; padding: 40px 20px; margin: 0; line-height: 1.6; }
        .box { max-width: 900px; margin: 0 auto; background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 28px 32px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        h1 { color: #ef4444; font-size: 1.6rem; margin-top: 0; }
        .msg { background: #fee2e2; color: #991b1b; padding: 12px 16px; border-radius: 6px; font-weight: bold; font-size: 1.05rem; word-break: break-word; }
        .meta { color: #94a3b8; font-size: 0.9rem; margin: 16px 0; }
        pre { background: #0f172a; color: #e2e8f0; padding: 16px; border-radius: 8px; overflow-x: auto; font-size: 0.82rem; font-family: monospace; }
        .btn { display: inline-block; background: #0284c7; color: white; padding: 8px 18px; border-radius: 6px; text-decoration: none; font-weight: bold; margin-top: 16px; }
    </style>
</head>
<body>
    <div class="box">
        <h1> 500 Server Error Details</h1>
        <div class="msg">
            {{ $exception->getMessage() ?: 'No exception message provided.' }}
        </div>
        <div class="meta">
            @if(method_exists($exception, 'getFile'))
                <strong>File:</strong> {{ $exception->getFile() }} : Line {{ $exception->getLine() }}
            @endif
        </div>
        @if(method_exists($exception, 'getTraceAsString'))
            <details open style="margin-top: 16px;">
                <summary style="cursor: pointer; color: #38bdf8; font-weight: bold;">Stack Trace</summary>
                <pre>{{ $exception->getTraceAsString() }}</pre>
            </details>
        @endif
        <a href="/" class="btn">&larr; Back to Home</a>
    </div>
</body>
</html>
