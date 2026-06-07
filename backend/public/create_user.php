<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/cors.php';
require_once __DIR__ . '/../includes/response_helper.php';

$input = json_decode(file_get_contents("php://input"), true);
$username = $input['username'] ?? '';
$password = $input['password'] ?? '';
$role = $input['role'] ?? 'user';

if (!$username || !$password) {
    sendError("Username and password are required");
}

if (usernameExists($username)) {
    sendError("Username already exists");
}

if (!roleExists($role)) {
    sendError("Role does not exist");
}

if (createUser($username, $password, $role)) {
    sendSuccess(null, "User created successfully", 201);
} else {
    sendError("Failed to create user", 500);
}
