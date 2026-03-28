<?php
/* * Video Fetcher - Professional Edition
 * Combined Logic and UI
 */

$videoUrl = "";
$error = "";

if (isset($_POST['url'])) {
    $input = $_POST['url'];
    
    // Extract ID from URL or use raw input
    if (filter_var($input, FILTER_VALIDATE_URL)) {
        $urlParts = parse_url($input);
        parse_str($urlParts['query'] ?? '', $query);
        $videoId = $query['id'] ?? basename($urlParts['path']);
    } else {
        $videoId = $input;
    }

    if (!empty($videoId)) {
        $userAgent = 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Mobile Safari/537.36';
        $embedUrl = "https://vidtronx.com/embed.php?bucket=temporary&id=" . htmlspecialchars($videoId);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $embedUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        curl_setopt($ch, CURLOPT_REFERER, "https://vidtronx.com/");
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $html = curl_exec($ch);
        curl_close($ch);

        if (preg_match('/<source src="(https:\/\/vidoycdn\.b-cdn\.net[^"]+)"/', $html, $matches)) {
            $videoUrl = str_replace('&amp;', '&', $matches[1]);
            header("Location: " . $videoUrl);
            exit;
        } else {
            $error = "Unable to locate video source. The ID might be invalid or expired.";
        }
    } else {
        $error = "Please enter a valid Video URL or ID.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Stream Fetcher</title>
    <style>
        :root {
            --bg-color: #0f172a;
            --card-bg: #1e293b;
            --accent-color: #38bdf8;
            --accent-hover: #0ea5e9;
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --error-color: #ef4444;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-primary);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .container {
            width: 100%;
            max-width: 450px;
            padding: 20px;
        }

        .card {
            background-color: var(--card-bg);
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3), 0 8px 10px -6px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.05);
            text-align: center;
        }

        h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
            letter-spacing: -0.025em;
        }

        p {
            color: var(--text-secondary);
            font-size: 14px;
            margin-bottom: 32px;
        }

        .input-group {
            margin-bottom: 20px;
            text-align: left;
        }

        label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 8px;
            color: var(--text-secondary);
            letter-spacing: 0.05em;
        }

        input[type="text"] {
            width: 100%;
            padding: 14px 16px;
            border-radius: 8px;
            border: 1px solid #334155;
            background-color: #0f172a;
            color: white;
            font-size: 15px;
            box-sizing: border-box;
            transition: all 0.2s ease;
        }

        input[type="text"]:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.2);
        }

        button {
            width: 100%;
            padding: 14px;
            border-radius: 8px;
            border: none;
            background-color: var(--accent-color);
            color: #0f172a;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        button:hover {
            background-color: var(--accent-hover);
            transform: translateY(-1px);
        }

        button:active {
            transform: translateY(0);
        }

        .error-message {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--error-color);
            padding: 12px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 20px;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .footer {
            margin-top: 24px;
            font-size: 12px;
            color: #475569;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <h1>Video Downloader</h1>
        <p>Enter the video link or ID to start fetching the media file.</p>

        <?php if ($error): ?>
            <div class="error-message">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="input-group">
                <label for="url">Resource Identifier</label>
                <input type="text" id="url" name="url" placeholder="https://vidtronx.com/e/..." required>
            </div>
            <button type="submit">Download Media</button>
        </form>

        <div class="footer">
            SECURE ENCRYPTED FETCHING
        </div>
    </div>
</div>

</body>
</html>
