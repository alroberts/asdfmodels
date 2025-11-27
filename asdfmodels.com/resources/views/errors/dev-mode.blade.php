<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error - ASDF Models</title>
    <style>
        body {
            font-family: system-ui, -apple-system, sans-serif;
            padding: 20px;
            background: #f3f4f6;
        }
        .error-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border: 2px solid #dc2626;
            border-radius: 8px;
            padding: 24px;
        }
        .error-header {
            color: #dc2626;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 16px;
        }
        .error-section {
            margin-bottom: 20px;
            padding: 16px;
            background: #fef2f2;
            border-left: 4px solid #dc2626;
            border-radius: 4px;
        }
        .error-section h3 {
            margin-top: 0;
            color: #991b1b;
            font-size: 18px;
        }
        .error-section pre {
            background: #1f2937;
            color: #f9fafb;
            padding: 16px;
            border-radius: 4px;
            overflow-x: auto;
            font-size: 12px;
            line-height: 1.5;
        }
        .error-section code {
            background: #1f2937;
            color: #f9fafb;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 12px;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #dc2626;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        .back-link:hover {
            background: #b91c1c;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-header">⚠️ Error (Developer Mode Enabled)</div>
        
        <div class="error-section">
            <h3>Error Message</h3>
            <p><code>{{ $message }}</code></p>
        </div>

        <div class="error-section">
            <h3>Location</h3>
            <p><strong>File:</strong> <code>{{ $file }}</code></p>
            <p><strong>Line:</strong> <code>{{ $line }}</code></p>
        </div>

        @if(isset($trace))
        <div class="error-section">
            <h3>Stack Trace</h3>
            <pre>{{ $trace }}</pre>
        </div>
        @endif

        <a href="{{ url()->previous() }}" class="back-link">← Go Back</a>
    </div>
</body>
</html>

