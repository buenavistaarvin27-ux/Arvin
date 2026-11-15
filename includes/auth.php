<?php

// Start the session securely
if (session_status() === PHP_SESSION_NONE) {

    $secure = isset($_SERVER['HTTPS']) &&
              $_SERVER['HTTPS'] !== 'off';

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}

// Protect admin pages
function requireAdmin() {

    if (!isset($_SESSION['admin_id'])) {
        header("Location: login.php");
        exit;
    }

}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

?>
