<?php
require_once __DIR__ . '/../includes/jwt_helper.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/cors.php';
require_once __DIR__ . '/../includes/response_helper.php';

$input = json_decode(file_get_contents("php://input"), true);
$username = $input['username'] ?? '';
$password = $input['password'] ?? '';

if (!$username || !$password) {
    sendError("Username and password are required");
}

$user = authenticateUser($username, $password);

if ($user) {
    $token_data = generateAccessToken($user);
    $refreshToken = generateRefreshToken();
    
    if (storeRefreshToken($username, $refreshToken, time() + REFRESH_TOKEN_EXP)) {
        sendSuccess([
            "access_token" => $token_data['token'],
            "access_token_expiry" => $token_data['expiry'],
            "refresh_token" => $refreshToken,
            "username" => $user['username'],
        ]);
    } else {
        sendError("Failed to store session", 500);
    }
} else {
    sendError("Invalid credentials", 401);
}
