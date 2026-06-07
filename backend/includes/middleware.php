<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/jwt_helper.php';

header('Content-Type: application/json');

$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    http_response_code(401);
    echo json_encode(["error" => "Missing or invalid Authorization header"]);
    exit;
}

$token = $matches[1];

try {
    $decoded = verifyAccessToken($token);
    $user = json_decode(json_encode($decoded), true);
    // You might want to store $user in $_SESSION or a request attribute
    // for use in the actual page.
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["error" => "Invalid or expired token", "details" => $e->getMessage()]);
    exit;
}
