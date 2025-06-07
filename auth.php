<?php
function authenticate() {
    error_log("[AUTH] Starting authentication process");
    
    // Get 'accessToken' from req cookies
    $access_token = $_COOKIE['accessToken'] ?? null;
    if (!$access_token) {
        error_log("[AUTH] No access token found in cookies");
        return null;
    }
    error_log("[AUTH] Access token found: " . substr($access_token, 0, 20) . "...");
    
    // Get 'refreshToken' from req cookies (optional)
    $refresh_token = $_COOKIE['refreshToken'] ?? null;
    if ($refresh_token) {
        error_log("[AUTH] Refresh token found: " . substr($refresh_token, 0, 20) . "...");
    } else {
        error_log("[AUTH] No refresh token found in cookies");
    }    
    // Fetch user from main server
    $backend_url = BACKEND_URL . '/api/user/from/jwt/raw';
    $url = $backend_url . '?accessToken=' . urlencode($access_token);
    
    // Add refreshToken if available
    if ($refresh_token) {
        $url .= '&refreshToken=' . urlencode($refresh_token);
    }
    
    error_log("[AUTH] Making request to: " . $url);
    
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
    
    if ($response === false) {
        error_log("[AUTH] Failed to get response from server");
        error_log("[AUTH] Last error: " . error_get_last()['message'] ?? 'Unknown error');
        return null;
    }
    
    error_log("[AUTH] Raw response received: " . $response);    
    error_log("[AUTH] Raw response received: " . $response);
    
    $data = json_decode($response, true);
    if (!$data) {
        error_log("[AUTH] Failed to decode JSON response");
        return null;
    }
    
    error_log("[AUTH] Decoded response: " . json_encode($data));
    
    if (!isset($data['success']) || !$data['success']) {
        error_log("[AUTH] Response indicates failure");
        if (isset($data['error'])) {
            error_log("[AUTH] Error from server: " . $data['error']);
        }
        if (isset($data['message'])) {
            error_log("[AUTH] Message from server: " . $data['message']);
        }
        return null;
    }
    
    // Extract user from the response structure
    $user = $data['user'] ?? null;
    if (!$user) {
        error_log("[AUTH] No user data in response");
        return null;
    }
    
    if (!isset($user['userId'])) {
        error_log("[AUTH] User data missing userId field");
        error_log("[AUTH] Available user fields: " . implode(', ', array_keys($user)));
        return null;
    }
    
    error_log("[AUTH] Authentication successful for userId: " . $user['userId']);
    return $user;
}
