<?php
require_once 'config.php';
require_once 'auth.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . FRONTEND_URL);
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

// Health Check:
if ($path === '/health') {
    echo json_encode(['status' => 'ok']);
    exit;
}

// File Server:
if ($_SERVER['REQUEST_METHOD'] === 'GET' && preg_match('/^\/([a-zA-Z0-9\-]+)\/([a-f0-9]+\.(png|jpg|jpeg|gif|webp))$/i', $path, $matches)) {
    $user_id = $matches[1];
    $filename = $matches[2];
    
    $file_path = DATA_DIR . "/{$user_id}/{$filename}";
    
    if (!file_exists($file_path)) {
        http_response_code(404);
        echo json_encode(['error' => 'File not found']);
        exit;
    }
      $mime_type = mime_content_type($file_path);
    header("Content-Type: {$mime_type}");
    header("Content-Length: " . filesize($file_path));
    
    // Cache can last long time cause files are UUID-based and immutable
    header("Cache-Control: public, max-age=31536000, immutable"); // 1 year
    header("Expires: " . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
    header("ETag: \"" . md5_file($file_path) . "\"");
    
    readfile($file_path);
    exit;
}

// File Upload:
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $path === '/upload') {
    $user = authenticate();
    
    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    
    if (!isset($_FILES['file'])) {
        http_response_code(400);
        echo json_encode(['error' => 'No file uploaded']);
        exit;
    }
    
    $file = $_FILES['file'];
    
    // File-type validation
    $allowed_types = ['image/png', 'image/jpeg', 'image/jpg', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed_types)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid file type']);
        exit;
    }      
    
    // Create user dir if it doesn't exist
    $user_dir = DATA_DIR . "/{$user['userId']}";
    if (!is_dir($user_dir)) {
        if (!mkdir($user_dir, 0755, true)) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create user directory: ' . error_get_last()['message']]);
            exit;
        }
    }
    
    // Generate UUID
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $unique_id = bin2hex(random_bytes(16));
    $filename = "{$unique_id}.{$extension}";
    $file_path = "{$user_dir}/{$filename}";    if (move_uploaded_file($file['tmp_name'], $file_path)) {
        $file_url = BASE_URL . "/{$user['userId']}/{$filename}";
        echo json_encode([
            'success' => true,
            'url' => $file_url,
            'filename' => $filename
        ]);
    } else {
        $error_msg = error_get_last();
        http_response_code(500);
        echo json_encode(['error' => 'Failed to upload file: ' . ($error_msg ? $error_msg['message'] : 'Unknown error')]);
    }
    exit;
}

// Default 404
http_response_code(404);
echo json_encode(['error' => 'Not found']);
