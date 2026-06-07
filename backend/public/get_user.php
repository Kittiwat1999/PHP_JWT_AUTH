<?php
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/../includes/cors.php";
require_once __DIR__ . "/../includes/middleware.php";
require_once __DIR__ . "/../includes/response_helper.php";

header('Content-Type: application/json');
$user_id = $user['id'] ?? '';
function getUserInfo(string $user_id) {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        throw new Exception("Error fetching user info: " . $e->getMessage());
    }
}
    $userInfo = getUserInfo($user_id);
    sendSuccess($userInfo);
