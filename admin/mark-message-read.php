<?php
require_once __DIR__ . "/../includes/auth.php";
requireAdmin();

require_once __DIR__ . "/../config/database.php";

// Allow POST requests only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: messages.php");
    exit;
}

// Validate CSRF token
$csrfToken = $_POST['csrf_token'] ?? '';

if (
    empty($csrfToken) ||
    !hash_equals($_SESSION['csrf_token'], $csrfToken)
) {
    header("Location: messages.php?status=invalid_token");
    exit;
}

// Validate message ID
$messageId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$messageId || $messageId < 1) {
    header("Location: messages.php?status=invalid_id");
    exit;
}

try {
    // Mark the selected message as read
    $stmt = $pdo->prepare("
        UPDATE contact_messages
        SET status = 'read'
        WHERE id = :id
    ");

    $stmt->execute([
        ':id' => $messageId
    ]);

    header("Location: messages.php?status=read");
    exit;

} catch (PDOException $e) {
    error_log("Marking contact message as read failed: " . $e->getMessage());

    header("Location: messages.php?status=error");
    exit;
}
