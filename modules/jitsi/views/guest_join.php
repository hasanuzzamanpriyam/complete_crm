<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Video Meeting - <?= htmlspecialchars($meeting_info->topic) ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-primary: #0f172a;
            --bg-secondary: #1e1b4b;
            --accent-primary: #6366f1;
            --accent-secondary: #4f46e5;
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --glass-bg: rgba(30, 41, 59, 0.6);
            --glass-border: rgba(255, 255, 255, 0.08);
            --glow-color: rgba(99, 102, 241, 0.25);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-secondary) 100%);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            overflow-x: hidden;
            position: relative;
        }

        /* Ambient glowing background circles */
        .ambient-glow {
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.15) 0%, rgba(99, 102, 241, 0) 70%);
            top: 10%;
            left: 10%;
            pointer-events: none;
            z-index: 0;
        }

        .ambient-glow-2 {
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(236, 72, 153, 0.08) 0%, rgba(236, 72, 153, 0) 70%);
            bottom: 10%;
            right: 10%;
            pointer-events: none;
            z-index: 0;
        }

        .container {
            width: 100%;
            max-width: 480px;
            z-index: 1;
            position: relative;
        }

        /* Glassmorphic Card */
        .card {
            background: var(--glass-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            text-align: center;
            animation: fadeIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo-area {
            width: 80px;
            height: 80px;
            background: rgba(99, 102, 241, 0.1);
            border: 1px solid rgba(99, 102, 241, 0.3);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 32px;
            color: var(--accent-primary);
            box-shadow: 0 0 20px var(--glow-color);
            animation: pulse 3s infinite ease-in-out;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 0 20px var(--glow-color);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 0 30px rgba(99, 102, 241, 0.4);
            }
        }

        h1 {
            font-family: 'Outfit', sans-serif;
            font-weight: 600;
            font-size: 28px;
            letter-spacing: -0.5px;
            margin-bottom: 8px;
            background: linear-gradient(to right, #ffffff, #c7d2fe);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .subtitle {
            color: var(--text-secondary);
            font-size: 14px;
            margin-bottom: 32px;
        }

        /* Meeting Details block */
        .meeting-info {
            background: rgba(15, 23, 42, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.03);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 32px;
            text-align: left;
        }

        .info-row {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
            font-size: 14px;
        }

        .info-row:last-child {
            margin-bottom: 0;
        }

        .info-icon {
            width: 32px;
            height: 32px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            color: var(--accent-primary);
            flex-shrink: 0;
        }

        .info-label {
            color: var(--text-secondary);
            font-weight: 500;
            margin-right: 8px;
        }

        .info-value {
            color: var(--text-primary);
            font-weight: 600;
        }

        /* Form Controls */
        .form-group {
            margin-bottom: 24px;
            text-align: left;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            font-size: 16px;
            transition: color 0.3s;
        }

        input {
            width: 100%;
            background: rgba(15, 23, 42, 0.6);
            border: 1.5px solid var(--glass-border);
            border-radius: 14px;
            padding: 16px 16px 16px 48px;
            color: #ffffff;
            font-family: inherit;
            font-size: 15px;
            font-weight: 500;
            outline: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        input:focus {
            border-color: var(--accent-primary);
            background: rgba(15, 23, 42, 0.8);
            box-shadow: 0 0 0 4px var(--glow-color);
        }

        input:focus + .input-icon {
            color: var(--accent-primary);
        }

        /* Join Button */
        button {
            width: 100%;
            background: linear-gradient(135deg, var(--accent-primary) 0%, var(--accent-secondary) 100%);
            border: none;
            border-radius: 14px;
            padding: 16px;
            color: #ffffff;
            font-family: inherit;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 20px rgba(99, 102, 241, 0.3);
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.5);
            background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
        }

        button:active {
            transform: translateY(0);
        }

        .footer {
            margin-top: 24px;
            font-size: 12px;
            color: var(--text-secondary);
        }

        .footer a {
            color: var(--accent-primary);
            text-decoration: none;
            font-weight: 500;
        }

        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="ambient-glow"></div>
    <div class="ambient-glow-2"></div>

    <div class="container">
        <div class="card">
            <div class="logo-area">
                <i class="fa-solid fa-video"></i>
            </div>
            
            <h1>Ready to Join?</h1>
            <p class="subtitle">Enter your name below to connect to the meeting room.</p>
            
            <div class="meeting-info">
                <div class="info-row">
                    <div class="info-icon">
                        <i class="fa-solid fa-heading"></i>
                    </div>
                    <div>
                        <span class="info-label">Topic:</span>
                        <span class="info-value"><?= htmlspecialchars($meeting_info->topic) ?></span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-icon">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                    <div>
                        <span class="info-label">Scheduled:</span>
                        <span class="info-value"><?= date('M d, Y - h:i A', strtotime($meeting_info->meeting_time)) ?></span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-icon">
                        <i class="fa-solid fa-hourglass-half"></i>
                    </div>
                    <div>
                        <span class="info-label">Duration:</span>
                        <span class="info-value"><?= htmlspecialchars($meeting_info->duration) ?> Minutes</span>
                    </div>
                </div>
            </div>

            <form action="" method="post">
                <div class="form-group">
                    <label for="guest_name">Your Name</label>
                    <div class="input-wrapper">
                        <input type="text" id="guest_name" name="guest_name" placeholder="e.g. John Doe" required autofocus autocomplete="off">
                        <i class="fa-solid fa-user input-icon"></i>
                    </div>
                </div>

                <button type="submit">
                    <span>Join Meeting</span>
                    <i class="fa-solid fa-arrow-right-to-bracket"></i>
                </button>
            </form>

            <div class="footer">
                Powered by <a href="#"><?= config_item('company_name') ?> Video</a>
            </div>
        </div>
    </div>
</body>
</html>
