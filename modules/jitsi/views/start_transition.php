<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Starting Jitsi Meeting...</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Outfit', sans-serif;
            color: #ffffff;
            overflow: hidden;
        }
        .container {
            text-align: center;
            max-width: 480px;
            padding: 40px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 24px;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            animation: fadeIn 0.8s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .spinner {
            width: 64px;
            height: 64px;
            border: 5px solid rgba(255, 255, 255, 0.1);
            border-top-color: #6366f1;
            border-radius: 50%;
            margin: 0 auto 30px auto;
            animation: spin 1s linear infinite;
            box-shadow: 0 0 15px rgba(99, 102, 241, 0.3);
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        h2 {
            font-weight: 600;
            font-size: 24px;
            margin-bottom: 12px;
            background: linear-gradient(to right, #ffffff, #c7d2fe);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        p {
            font-weight: 300;
            font-size: 15px;
            color: #94a3b8;
            line-height: 1.6;
            margin-bottom: 0;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container">
        <div class="spinner"></div>
        <h2>Starting Your Meeting</h2>
        <p>Notifying all invited users and clients in the background. Please wait while we connect you to Jitsi...</p>
    </div>
    
    <script type="text/javascript">
        $(document).ready(function() {
            // Trigger the email notifications via AJAX
            $.ajax({
                url: "<?= base_url('admin/jitsi/send_start_notifications_ajax/' . $jitsi_meeting_id) ?>",
                type: "GET",
                dataType: "json",
                complete: function() {
                    // Redirect to the actual Jitsi room immediately after AJAX completes
                    window.location.href = "<?= $meeting_url ?>";
                }
            });
        });
    </script>
</body>
</html>
