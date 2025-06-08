<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://gigtree.isdev.co');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

require_once 'config.php';
require_once 'auth.php';

$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

error_log("[REQUEST] Method: " . $_SERVER['REQUEST_METHOD'] . ", Path: " . $path);

// Debug: Check if path matches file server pattern
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $pattern = '/^\/(\d+)\/([a-f0-9\-]+\.(png|jpg|jpeg|gif|webp))$/i';
    $matches_pattern = preg_match($pattern, $path);
    error_log("[DEBUG] GET request - Path: $path, Matches pattern: " . ($matches_pattern ? 'YES' : 'NO'));
}

// Health Check:
if ($path === '/health') {
    echo json_encode(['status' => 'ok']);
    exit;
}

// File Server:
if ($_SERVER['REQUEST_METHOD'] === 'GET' && preg_match('/^\/(\d+)\/([a-f0-9\-]+\.(png|jpg|jpeg|gif|webp))$/i', $path, $matches)) {
    $user_id = $matches[1];
    $filename = $matches[2];
    
    error_log("[FILE_SERVER] Requested path: " . $path);
    error_log("[FILE_SERVER] User ID: " . $user_id);
    error_log("[FILE_SERVER] Filename: " . $filename);
    
    $file_path = DATA_DIR . "/{$user_id}/{$filename}";
    error_log("[FILE_SERVER] Looking for file at: " . $file_path);
    error_log("[FILE_SERVER] File exists: " . (file_exists($file_path) ? 'YES' : 'NO'));
    
    if (!file_exists($file_path)) {
        http_response_code(404);
        echo json_encode(['error' => 'File not found']);
        exit;
    }
    
    $mime_type = mime_content_type($file_path);
    header("Content-Type: {$mime_type}");
    header("Content-Length: " . filesize($file_path));
    readfile($file_path);
    exit;
}

// File Upload:
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $path === '/upload') {
    error_log("[UPLOAD] Starting file upload process");
    
    $user = authenticate();
    
    if (!$user) {
        error_log("[UPLOAD] Authentication failed");
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    
    error_log("[UPLOAD] User authenticated: " . json_encode($user));
    
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
        mkdir($user_dir, 0755, true);
    }
    
    // Generate UUID
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $unique_id = bin2hex(random_bytes(16));
    $filename = "{$unique_id}.{$extension}";
    $file_path = "{$user_dir}/{$filename}";    if (move_uploaded_file($file['tmp_name'], $file_path)) {
        $file_url = BASE_URL . "/{$user['userId']}/{$filename}";
        error_log("[UPLOAD] File uploaded successfully to: " . $file_path);
        error_log("[UPLOAD] Generated URL: " . $file_url);
        error_log("[UPLOAD] Generated filename: " . $filename);
        echo json_encode([
            'success' => true,
            'url' => $file_url,
            'filename' => $filename
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to upload file']);
    }
    exit;
}

// Default 404
http_response_code(404);
echo json_encode(['error' => 'Not found']);
