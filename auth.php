<?php
function authenticate() {
    // Get 'accessToken' from req cookies
    $access_token = $_COOKIE['accessToken'] ?? null;
    if (!$access_token) return null;
    
    // Fetch user from main server
    $backend_url = BACKEND_URL . '/api/user/from/jwt/raw';
    $url = $backend_url . '?accessToken=' . urlencode($access_token);
    
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => [
                'Accept: application/json',
            ],
            'timeout' => 10
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    if ($response === false) return null;
    
    $user = json_decode($response, true);
    if (!$user || !isset($user['id'])) return null;
    
    return $user;
}
