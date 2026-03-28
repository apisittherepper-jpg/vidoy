<?php
/*
 * Video Fetcher & Proxy Downloader - Professional Edition
 * Single File Solution
 */

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['url'])) {
    $input = trim($_POST['url']);
    
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

        // Step 1: Fetch the embed page to extract the CDN link
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $embedUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        curl_setopt($ch, CURLOPT_REFERER, $input);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $html = curl_exec($ch);
        curl_close($ch);

        if (preg_match('/<source src="(https:\/\/vidoycdn\.b-cdn\.net[^"]+)"/', $html, $matches)) {
            $videoUrl = str_replace('&amp;', '&', $matches[1]);
            
            // Step 2: Proxy the download to bypass Referer checks and force file download
            // Clean output buffer to prevent file corruption
            if (ob_get_level()) {
                ob_end_clean();
            }

            header('Content-Description: File Transfer');
            header('Content-Type: video/mp4');
            header('Content-Disposition: attachment; filename="video_' . $videoId . '.mp4"');
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: 0');

            $chProxy = curl_init();
            curl_setopt($chProxy, CURLOPT_URL, $videoUrl);
            curl_setopt($chProxy, CURLOPT_RETURNTRANSFER, false); // Stream directly to output
            curl_setopt($chProxy, CURLOPT_HTTPHEADER, array(
                'Referer: https://vidtronx.com/',
                'User-Agent: ' . $userAgent
            ));
            curl_setopt($chProxy, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($chProxy, CURLOPT_SSL_VERIFYPEER, false);
            
            // Execute stream
            curl_exec($chProxy);
            curl_close($chProxy);
            
            // Exit immediately so HTML UI does not append to the video file
            exit;
        } else {
            $error = "Unable to locate video source. The media might have been removed or access is restricted.";
        }
    } else {
        $error = "Invalid format. Please enter a valid Resource URL or ID.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Media Stream Fetcher</title>
    <style>
        :root {
            --bg-color: #0f172a;
            --card-bg: #1e293b;
            --accent-color: #38bdf8;
            --accent-hover: #0ea5e9;
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --error-color: #ef4444;
            --input-bg: #0f172a;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-primary);
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .container {
            width: 100%;
            max-width: 420px;
            padding: 20px;
            box-sizing: border-box;
        }

        .card {
            background-color: var(--card-bg);
            padding: 40px 30px;
            border-radius: 12px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2), 0 10px 10px -5px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.05);
            text-align: center;
        }

        h1 {
            font-size: 22px;
            font-weight: 600;
            margin: 0 0 10px 0;
            letter-spacing: 0.5px;
        }

        p {
            color: var(--text-secondary);
            font-size: 14px;
            margin: 0 0 30px 0;
            line-height: 1.5;
        }

        .input-group {
            margin-bottom: 20px;
            text-align: left;
        }

        label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 8px;
            color: var(--text-secondary);
            letter-spacing: 1px;
        }

        input[type="text"] {
            width: 100%;
            padding: 14px 16px;
            border-radius: 8px;
            border: 1px solid #334155;
            background-color: var(--input-bg);
            color: var(--text-primary);
            font-size: 14px;
            box-sizing: border-box;
            transition: all 0.2s ease;
        }

        input[type="text"]:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.2);
        }

        input[type="text"]::placeholder {
            color: #475569;
        }

        button {
            width: 100%;
            padding: 14px;
            border-radius: 8px;
            border: none;
            background-color: var(--accent-color);
            color: #0f172a;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s ease, transform 0.1s ease;
        }

        button:hover {
            background-color: var(--accent-hover);
        }

        button:active {
            transform: scale(0.98);
        }

        .error-message {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--error-color);
            padding: 12px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 20px;
            border: 1px solid rgba(239, 68, 68, 0.2);
            text-align: left;
        }

        .footer {
            margin-top: 25px;
            font-size: 11px;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <h1>Media Fetcher</h1>
        <p>Enter the target URL or resource ID to securely download the media file.</p>

        <?php if ($error): ?>
            <div class="error-message">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="input-group">
                <label for="url">Resource Link</label>
                <input type="text" id="url" name="url" placeholder="https://..." required autocomplete="off">
            </div>
            <button type="submit">Download Media</button>
        </form>

        <div class="footer">
            Secured Proxy Connection
        </div>
    </div>
</div>

</body>
</html>
