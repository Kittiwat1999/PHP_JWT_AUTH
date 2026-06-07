<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function generateAccessToken(array $payload): array {
    $payload['exp'] = time() + ACCESS_TOKEN_EXP;
    return ["token" => JWT::encode($payload, JWT_SECRET, 'HS256'), "expiry" => $payload['exp']]; // Convert to milliseconds for JS Date.now()
}

function generateRefreshToken(): string {
    return bin2hex(random_bytes(32)); // Secure random string
}

function verifyAccessToken(string $token): object {
    return JWT::decode($token, new Key(JWT_SECRET, 'HS256'));
}
