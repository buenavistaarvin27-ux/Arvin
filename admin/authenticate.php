
<?php

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../includes/auth.php";

// Allow POST requests only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit;
}

// Validate CSRF token
$token = $_POST['csrf_token'] ?? '';

if (!hash_equals($_SESSION['csrf_token'], $token)) {
    die("Invalid request.");
}

// Get login information
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Validate input
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
    header("Location: login.php?error=1");
    exit;
}

try {

    // Find admin account
    $sql = "SELECT id, username, email, password_hash
            FROM admin_users
            WHERE email = :email
            LIMIT 1";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':email' => $email
    ]);

    $admin = $stmt->fetch();

    // Verify password
    if ($admin && password_verify($password, $admin['password_hash'])) {

        // Regenerate session ID
        session_regenerate_id(true);

        // Store admin session
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_email'] = $admin['email'];

        // Generate new CSRF token
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        // Update last login
        $update = $pdo->prepare(
            "UPDATE admin_users
             SET last_login = NOW()
             WHERE id = :id"
        );

        $update->execute([
            ':id' => $admin['id']
        ]);

        // Redirect to dashboard
        header("Location: dashboard.php");
        exit;

    } else {

        // Invalid credentials
        header("Location: login.php?error=1");
        exit;

    }

} catch (PDOException $e) {

    error_log($e->getMessage());

    http_response_code(500);
    exit("Unable to process login. Please try again.");

}

?>
