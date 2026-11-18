<?php
// Contact Form Submission Handler

require_once __DIR__ . '/config/database.php';

// Allow POST requests only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: contact.html");
    exit;
}

// Get and clean submitted values
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');

// Validate name
if (strlen($name) < 2 || strlen($name) > 150) {
    header("Location: contact.html?status=invalid_name");
    exit;
}

// Validate email
if (
    strlen($email) > 150 ||
    !filter_var($email, FILTER_VALIDATE_EMAIL)
) {
    header("Location: contact.html?status=invalid_email");
    exit;
}

// Validate message
if (strlen($message) < 10 || strlen($message) > 10000) {
    header("Location: contact.html?status=invalid_message");
    exit;
}

try {
    // Insert message into database
    $stmt = $pdo->prepare("
        INSERT INTO contact_messages
        (name, email, message)
        VALUES (:name, :email, :message)
    ");

    $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':message' => $message
    ]);

    // Redirect after successful submission
    header("Location: contact.html?status=success");
    exit;

} catch (PDOException $e) {
    // Record technical error in server log
    error_log("Contact form submission failed: " . $e->getMessage());

    // Show a generic error to the visitor
    header("Location: contact.html?status=error");
    exit;
}
