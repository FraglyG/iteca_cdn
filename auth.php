<?php
function authenticate() {
    // Get 'accessToken' from req cookies
    $access_token = $_COOKIE['accessToken'] ?? null;
    if (!$access_token) {
        return null;
    }
    
    // Get 'refreshToken' from req cookies (optional)
    $refresh_token = $_COOKIE['refreshToken'] ?? null;    // Fetch user from main server
    $backend_url = BACKEND_URL . '/api/user/from/jwt/raw';
    $url = $backend_url . '?accessToken=' . urlencode($access_token);
    
    // Add refreshToken if available
    if ($refresh_token) {
        $url .= '&refreshToken=' . urlencode($refresh_token);
    }
    
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => [
                'Accept: application/json',
            ],
            'timeout' => 10
        ]
    ]);      $response = @file_get_contents($url, false, $context);
    
    if ($response === false) {
        return null;
    }
    
    $data = json_decode($response, true);
    if (!$data) {
        return null;
    }
      if (!isset($data['success']) || !$data['success']) {
        return null;
    }
    
    // Extract user from the response structure
    $user = $data['user'] ?? null;
    if (!$user) {
        return null;
    }
    
    if (!isset($user['userId'])) {
        return null;
    }
    
    return $user;
}
