<!DOCTYPE html>
<html lang="<?php echo $this->escape($language ?? 'fr'); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $this->escape($statusCode ?? 'Error'); ?> - <?php echo $this->escape($pageTitle ?? 'Omist'); ?></title>
    <link rel="icon" type="image/png" href="./images/favicon.svg">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .error-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 40px;
            text-align: center;
            max-width: 600px;
        }
        .error-code {
            font-size: 72px;
            font-weight: bold;
            color: #e74c3c;
            margin-bottom: 20px;
        }
        .error-title {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
        }
        .error-message {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
        }
        .error-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
        }
        .btn {
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn-primary {
            background-color: #3498db;
            color: white;
            border: none;
        }
        .btn-primary:hover {
            background-color: #2980b9;
        }
        .btn-secondary {
            background-color: #ecf0f1;
            color: #3498db;
            border: 1px solid #bdc3c7;
        }
        .btn-secondary:hover {
            background-color: #d6dbdf;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code"><?php echo $this->escape($statusCode ?? 'Error'); ?></div>
        <div class="error-title">
            <?php 
            $title = '';
            switch ($statusCode ?? 0) {
                case 400:
                    $title = 'Bad Request';
                    break;
                case 401:
                    $title = 'Unauthorized';
                    break;
                case 403:
                    $title = 'Forbidden';
                    break;
                case 404:
                    $title = 'Page Not Found';
                    break;
                case 500:
                    $title = 'Internal Server Error';
                    break;
                case 503:
                    $title = 'Service Unavailable';
                    break;
                default:
                    $title = 'Error';
            }
            echo $this->escape($title);
            ?>
        </div>
        <div class="error-message">
            <?php echo $this->escape($message ?? 'An unexpected error occurred.'); ?>
        </div>
        <div class="error-actions">
            <a href="/" class="btn btn-primary">Return to Home</a>
            <a href="#" onclick="history.back()" class="btn btn-secondary">Go Back</a>
        </div>
    </div>
</body>
</html>
