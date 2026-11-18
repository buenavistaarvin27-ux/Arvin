<?php
require_once __DIR__ . "/../includes/auth.php";
requireAdmin();

require_once __DIR__ . "/../config/database.php";

// Get all contact messages
try {
    $stmt = $pdo->query("
        SELECT *
        FROM contact_messages
        ORDER BY created_at DESC
    ");

    $messages = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Contact messages loading failed: " . $e->getMessage());
    $messages = [];
    $databaseError = true;
}

// Safely display database values
function escape($value) {
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

$username = escape($_SESSION['admin_username'] ?? 'Admin');

// Status notifications
$statusMessages = [
    'read' => 'Message successfully marked as read.',
    'deleted' => 'Message successfully deleted.',
    'invalid_token' => 'Security validation failed. Please try again.',
    'invalid_id' => 'Invalid message ID.',
    'not_found' => 'The selected message was not found.',
    'error' => 'Something went wrong. Please try again.'
];

$status = $_GET['status'] ?? '';
$notification = $statusMessages[$status] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Contact Messages | Admin</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #101b16;
            color: #f1f5f2;
            min-height: 100vh;
            padding: 30px;
        }

        .container {
            max-width: 1200px;
            margin: auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 30px;
        }

        h1 {
            color: #a3e6b5;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #b5c5bb;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #a3e6b5;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .card {
            background: #1b2b22;
            border: 1px solid #304b39;
            border-radius: 12px;
            padding: 25px;
        }

        .card h2 {
            color: #a3e6b5;
            margin-bottom: 20px;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1100px;
        }

        th, td {
            padding: 14px;
            text-align: left;
            border-bottom: 1px solid #304b39;
            vertical-align: top;
        }

        th {
            color: #a3e6b5;
            background: #24382b;
        }

        td {
            color: #d4dfd7;
        }

        .message-preview {
            width: 100%;
            max-width: 350px;
            white-space: normal;
            overflow-wrap: anywhere;
            word-break: normal;
            text-align: left;
            line-height: 1.6;
        }

        th:nth-child(5),
        td:nth-child(5) {
            width: 30%;
            text-align: left;
        }

        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            white-space: nowrap;
        }

        .unread {
            background: #493d22;
            color: #f5d98a;
        }

        .read {
            background: #244b32;
            color: #a3e6b5;
        }

        .edit-btn {
            padding: 8px 12px;
            background: #8bc9a5;
            color: #102219;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            white-space: nowrap;
        }

        .edit-btn:hover {
            background: #a2dbb8;
        }

        .delete-btn {
            padding: 8px 12px;
            background: #8b3939;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            white-space: nowrap;
        }

        .delete-btn:hover {
            background: #a74747;
        }

        .actions {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }

        .empty-message {
            text-align: center;
            padding: 35px;
            color: #b5c5bb;
        }

        .error-message {
            color: #ffb5ae;
            padding: 20px;
            background: #49302d;
            border-radius: 8px;
        }

        .message-count {
            color: #b5c5bb;
            margin-bottom: 18px;
        }

        .email-link {
            color: #a3e6b5;
            text-decoration: none;
        }

        .email-link:hover {
            text-decoration: underline;
        }

        .notification {
            padding: 14px;
            margin-bottom: 20px;
            border-radius: 8px;
            background: #244b32;
            color: #a3e6b5;
        }

        .notification.error {
            background: #49302d;
            color: #ffb5ae;
        }

        @media (max-width: 600px) {
            body {
                padding: 15px;
            }

            .card {
                padding: 15px;
            }
        }
    </style>
</head>

<body>

<main class="container">

    <a href="dashboard.php" class="back-link">
        &larr; Back to Dashboard
    </a>

    <header class="header">
        <div>
            <h1>Contact Messages</h1>
            <p class="subtitle">
                Welcome, <?php echo $username; ?>.
                View messages submitted through your portfolio.
            </p>
        </div>
    </header>

    <section class="card">

        <h2>All Messages</h2>

        <?php if ($notification): ?>

            <div class="notification <?php echo in_array($status, ['invalid_token', 'invalid_id', 'not_found', 'error'], true) ? 'error' : ''; ?>">
                <?php echo escape($notification); ?>
            </div>

        <?php endif; ?>

        <p class="message-count">
            Total Messages: <?php echo count($messages); ?>
        </p>

        <?php if (!empty($databaseError)): ?>

            <p class="error-message">
                Unable to load contact messages.
                Please check your database connection.
            </p>

        <?php elseif (count($messages) > 0): ?>

            <div class="table-wrapper">

                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Subject</th>
                            <th>Message</th>
                            <th>Status</th>
                            <th>Date Received</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($messages as $message): ?>

                            <tr>
                                <td>
                                    <?php echo (int) $message['id']; ?>
                                </td>

                                <td>
                                    <?php echo escape($message['name']); ?>
                                </td>

                                <td>
                                    <a
                                        class="email-link"
                                        href="mailto:<?php echo escape($message['email']); ?>"
                                    >
                                        <?php echo escape($message['email']); ?>
                                    </a>
                                </td>

                                <td>
                                    <?php
                                    echo !empty($message['subject'])
                                        ? escape($message['subject'])
                                        : 'No subject';
                                    ?>
                                </td>

                                <td>
                                    <div class="message-preview">
                                        <?php echo escape($message['message']); ?>
                                    </div>
                                </td>

                                <td>
                                    <span class="status <?php echo escape($message['status']); ?>">
                                        <?php echo escape(ucfirst($message['status'])); ?>
                                    </span>
                                </td>

                                <td>
                                    <?php
                                    echo !empty($message['created_at'])
                                        ? escape(date(
                                            'M d, Y h:i A',
                                            strtotime($message['created_at'])
                                        ))
                                        : 'N/A';
                                    ?>
                                </td>

                                <td>
                                    <div class="actions">

                                        <?php if ($message['status'] === 'unread'): ?>

                                            <form
                                                method="POST"
                                                action="mark-message-read.php"
                                            >
                                                <input
                                                    type="hidden"
                                                    name="csrf_token"
                                                    value="<?php echo escape($_SESSION['csrf_token']); ?>"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="id"
                                                    value="<?php echo (int) $message['id']; ?>"
                                                >

                                                <button type="submit" class="edit-btn">
                                                    Mark as Read
                                                </button>
                                            </form>

                                        <?php else: ?>

                                            <span class="status read">
                                                Already Read
                                            </span>

                                        <?php endif; ?>

                                        <form
                                            method="POST"
                                            action="delete-message.php"
                                            onsubmit="return confirm('Are you sure you want to permanently delete this message?');"
                                        >
                                            <input
                                                type="hidden"
                                                name="csrf_token"
                                                value="<?php echo escape($_SESSION['csrf_token']); ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?php echo (int) $message['id']; ?>"
                                            >

                                            <button type="submit" class="delete-btn">
                                                Delete
                                            </button>
                                        </form>

                                    </div>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    </tbody>
                </table>

            </div>

        <?php else: ?>

            <div class="empty-message">
                <h3>No Messages Found</h3>
                <p>Messages submitted through your contact form will appear here.</p>
            </div>

        <?php endif; ?>

    </section>

</main>

</body>
</html>
