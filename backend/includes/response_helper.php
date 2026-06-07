<?php

/**
 * Standardized JSON response helper.
 */
function sendResponse(bool $success, $data = null, string $message = '', int $statusCode = 200) {
    header('Content-Type: application/json');
    http_response_code($statusCode);
    
    $response = [
        'success' => $success
    ];

    if ($message) {
        $response['message'] = $message;
    }

    if ($data !== null) {
        if (is_array($data)) {
            $response = array_merge($response, ['data' => $data]);
        } else {
            $response['data'] = $data;
        }
    }

    echo json_encode($response);
    exit;
}

function sendError(string $message, int $statusCode = 400, $data = null) {
    sendResponse(false, $data, $message, $statusCode);
}

function sendSuccess($data = null, string $message = '', int $statusCode = 200) {
    sendResponse(true, $data, $message, $statusCode);
}
