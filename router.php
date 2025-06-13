<?php
// Mock router for development purposes

// Static file lookup
$uri = $_SERVER['REQUEST_URI'];
if (preg_match('#^/([a-zA-Z0-9\-]+)/([a-f0-9]+\.(png|jpg|jpeg|gif|webp))$#i', $uri, $matches)) {
    $static_path = __DIR__ . '/data/' . $matches[1] . '/' . $matches[2];
    if (file_exists($static_path) && !is_dir($static_path)) return false;
}

// API endpoint lookup
if (file_exists(__DIR__ . $uri) && !is_dir(__DIR__ . $uri)) return false;
include __DIR__ . '/index.php';