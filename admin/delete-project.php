<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

require_once __DIR__ . '/../config/database.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: projects.php");
    exit;
}

// Verify CSRF token
$submittedToken = $_POST['csrf_token'] ?? '';
$sessionToken = $_SESSION['csrf_token'] ?? '';

if (
    !is_string($submittedToken) ||
    !is_string($sessionToken) ||
    $sessionToken === '' ||
    !hash_equals($sessionToken, $submittedToken)
) {
    die("Invalid security token. Please refresh the page.");
}

// Validate project ID
$projectId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$projectId || $projectId < 1) {
    header("Location: projects.php?error=invalid_id");
    exit;
}

// Delete the selected project
try {
    $stmt = $pdo->prepare("DELETE FROM projects WHERE id = :id");
    $stmt->execute(['id' => $projectId]);

    if ($stmt->rowCount() > 0) {
        header("Location: projects.php?success=deleted");
        exit;
    }

    header("Location: projects.php?error=not_found");
    exit;

} catch (PDOException $e) {
    error_log("Project deletion failed: " . $e->getMessage());
    header("Location: projects.php?error=delete_failed");
    exit;
}
