<?php
require_once __DIR__ . '/../includes/jwt_helper.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/cors.php';
require_once __DIR__ . '/../includes/response_helper.php';

$input = json_decode(file_get_contents("php://input"), true);
$refreshToken = $input['refresh_token'] ?? '';

if (!$refreshToken) {
    sendError("Refresh token required");
}

$username = validateRefreshToken($refreshToken);

if (!$username) {
    sendError("Invalid or expired refresh token", 401);
}

// Rotate refresh token
revokeRefreshToken($refreshToken);

$newRefreshToken = generateRefreshToken();
$newExpiry = time() + REFRESH_TOKEN_EXP;

if (storeRefreshToken($username, $newRefreshToken, $newExpiry)) {
    $userInfo = getUserByUsername($username);
    $newAccessToken = generateAccessToken($userInfo);
    
    sendSuccess([
        "access_token" => $newAccessToken['token'],
        "refresh_token" => $newRefreshToken,
        "access_token_expiry" => $newAccessToken['expiry'],
        "username" => $userInfo['username']
    ]);
} else {
    sendError("Failed to update session", 500);
}
