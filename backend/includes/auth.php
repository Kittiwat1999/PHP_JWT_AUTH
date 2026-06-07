<?php
require_once __DIR__ . '/../config.php';

/**
 * Validates user credentials and returns user data.
 */
function authenticateUser(string $username, string $password): ?array {
    $user = getUserByUsername($username);
    if ($user && password_verify($password, $user['password'])) {
        unset($user['password']);
        return $user;
    }
    return null;
}

function getUserByUsername(string $username): ?array {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("
        SELECT U.id, U.username, U.password, R.name as role 
        FROM users U 
        INNER JOIN roles R ON U.role = R.id 
        WHERE U.username = ?
    ");
    $stmt->execute([$username]);
    return $stmt->fetch();
}

/**
 * Manages refresh tokens in the database.
 */
function storeRefreshToken(string $username, string $token, int $expiry): bool {
    $pdo = getDbConnection();
    $hash = hash_hmac('sha256', $token, JWT_SECRET);
    
    // Check if user already has a token
    $stmt = $pdo->prepare("SELECT id FROM refresh_tokens WHERE username = ?");
    $stmt->execute([$username]);
    
    if ($stmt->fetch()) {
        $stmt = $pdo->prepare("UPDATE refresh_tokens SET token_hash = ?, expires_at = ? WHERE username = ?");
        return $stmt->execute([$hash, $expiry, $username]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO refresh_tokens (token_hash, expires_at, username) VALUES (?, ?, ?)");
        return $stmt->execute([$hash, $expiry, $username]);
    }
}

function validateRefreshToken(string $token): ?string {
    $pdo = getDbConnection();
    $hash = hash_hmac('sha256', $token, JWT_SECRET);

    $stmt = $pdo->prepare("SELECT username, expires_at FROM refresh_tokens WHERE token_hash = ?");
    $stmt->execute([$hash]);
    $row = $stmt->fetch();

    if ($row && $row['expires_at'] > time()) {
        return $row['username'];
    }
    return null;
}

function revokeRefreshToken(string $token): bool {
    $pdo = getDbConnection();
    $hash = hash_hmac('sha256', $token, JWT_SECRET);
    $stmt = $pdo->prepare("DELETE FROM refresh_tokens WHERE token_hash = ?");
    return $stmt->execute([$hash]);
}

/**
 * User management helpers
 */
function usernameExists(string $username): bool {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("SELECT 1 FROM users WHERE username = ?");
    $stmt->execute([$username]);
    return (bool)$stmt->fetch();
}

function roleExists(string $roleName): bool {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("SELECT 1 FROM roles WHERE name = ?");
    $stmt->execute([$roleName]);
    return (bool)$stmt->fetch();
}

function createUser(string $username, string $password, string $roleName = 'user'): bool {
    $pdo = getDbConnection();
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("
        INSERT INTO users (username, password, role) 
        VALUES (?, ?, (SELECT id FROM roles WHERE name = ?))
    ");
    return $stmt->execute([$username, $hashedPassword, $roleName]);
}

