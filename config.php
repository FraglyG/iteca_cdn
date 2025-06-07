<?php
// Load .env
function loadEnv($path) {
    if (!file_exists($path)) return;
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

loadEnv(__DIR__ . '/.env');

// Configuration
define('DATA_DIR', __DIR__ . '/data');
define('BACKEND_URL', $_ENV['BACKEND_URL'] ?? 'http://localhost:3000');
define('BASE_URL', $_ENV['BASE_URL'] ?? 'http://localhost:8000');

// Create data directory if it doesn't exist
if (!is_dir(DATA_DIR)) mkdir(DATA_DIR, 0755, true);