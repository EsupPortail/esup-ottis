<?php
/**
 * Template de page de maintenance
 *
 * @package OMIST\Views\Errors
 */

// Définir le code HTTP
http_response_code(503);
header('Retry-After: 3600'); // 1 heure

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance | OMIST</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
            padding: 60px 40px;
            text-align: center;
            max-width: 600px;
            width: 100%;
            animation: fadeIn 0.5s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .icon {
            font-size: 80px;
            margin-bottom: 20px;
            animation: spin 2s linear infinite;
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .title {
            font-size: 32px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 15px;
        }
        .message {
            font-size: 18px;
            color: #7f8c8d;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        .countdown {
            font-size: 24px;
            font-weight: 600;
            color: #4facfe;
            margin: 20px 0;
        }
        .countdown span {
            display: inline-block;
            padding: 10px 15px;
            background: #f0f8ff;
            border-radius: 8px;
            margin: 0 5px;
            min-width: 60px;
        }
        .progress-container {
            width: 100%;
            height: 8px;
            background: #ecf0f1;
            border-radius: 4px;
            margin: 30px 0;
            overflow: hidden;
        }
        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #4facfe 0%, #00f2fe 100%);
            border-radius: 4px;
            width: 0%;
            animation: progress 30s linear forwards;
        }
        @keyframes progress {
            0% { width: 0%; }
            100% { width: 100%; }
        }
        .info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
            text-align: left;
        }
        .info h4 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .info p {
            color: #7f8c8d;
            font-size: 14px;
            line-height: 1.5;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #95a5a6;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">⚙️</div>
        <h1 class="title">Maintenance en cours</h1>
        <p class="message">
            Nous effectuons actuellement des travaux de maintenance pour améliorer nos services.
            <br>Nous serons de retour très bientôt.
        </p>

        <div class="countdown">
            <span id="hours">00</span>:
            <span id="minutes">30</span>:
            <span id="seconds">00</span>
        </div>

        <div class="progress-container">
            <div class="progress-bar"></div>
        </div>

        <div class="info">
            <h4>📋 Pourquoi cette maintenance ?</h4>
            <p>
                Nous mettons à jour nos systèmes pour offrir une meilleure expérience,
                améliorer les performances et corriger des bugs.
            </p>
            <h4 style="margin-top: 15px;">⏰ Quand serons-nous de retour ?</h4>
            <p>
                La maintenance devrait durer environ 30 à 60 minutes.
                Nous faisons de notre mieux pour revenir le plus vite possible.
            </p>
        </div>

        <div class="footer">
            OMIST Automatic Translator &copy; <?php echo date('Y'); ?>
        </div>
    </div>

    <script>
        // Compteur de maintenance
        let totalSeconds = 30 * 60; // 30 minutes

        function updateCountdown() {
            const hours = Math.floor(totalSeconds / 3600);
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            const seconds = totalSeconds % 60;

            document.getElementById('hours').textContent = hours.toString().padStart(2, '0');
            document.getElementById('minutes').textContent = minutes.toString().padStart(2, '0');
            document.getElementById('seconds').textContent = seconds.toString().padStart(2, '0');

            if (totalSeconds > 0) {
                totalSeconds--;
                setTimeout(updateCountdown, 1000);
            }
        }

        updateCountdown();

        // Actualiser la page périodiquement
        setTimeout(function() {
            location.reload();
        }, 300000); // 5 minutes
    </script>
</body>
</html>
