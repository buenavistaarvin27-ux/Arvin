<?php
require_once __DIR__ . "/../includes/auth.php";

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit("Method not allowed.");
}

// Verify CSRF token
$token = $_POST['csrf_token'] ?? '';
$sessionToken = $_SESSION['csrf_token'] ?? '';

if (
    $sessionToken === '' ||
    $token === '' ||
    !hash_equals($sessionToken, $token)
) {
    http_response_code(403);
    exit("Invalid request.");
}

// Clear all session data
$_SESSION = [];

// Remove the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Destroy the session
session_destroy();

// Return to login page
header("Location: login.php");
exit;
