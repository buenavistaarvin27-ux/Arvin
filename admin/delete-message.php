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
    // Delete selected contact message
    $stmt = $pdo->prepare("
        DELETE FROM contact_messages
        WHERE id = :id
    ");

    $stmt->execute([
        ':id' => $messageId
    ]);

    if ($stmt->rowCount() > 0) {
        header("Location: messages.php?status=deleted");
    } else {
        header("Location: messages.php?status=not_found");
    }

    exit;

} catch (PDOException $e) {
    error_log("Deleting contact message failed: " . $e->getMessage());

    header("Location: messages.php?status=error");
    exit;
}
