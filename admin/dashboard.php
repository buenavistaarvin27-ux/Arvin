
<?php
require_once __DIR__ . "/../includes/auth.php";
requireAdmin();

require_once __DIR__ . "/../config/database.php";

$username = htmlspecialchars(
    $_SESSION['admin_username'] ?? 'Admin',
    ENT_QUOTES,
    'UTF-8'
);

// Count unread contact messages
$unreadMessages = 0;

try {
    $stmt = $pdo->query("
        SELECT COUNT(*)
        FROM contact_messages
        WHERE status = 'unread'
    ");

    $unreadMessages = (int) $stmt->fetchColumn();

} catch (PDOException $e) {
    error_log("Unread message count failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Arvin's Portfolio</title>

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
            max-width: 1100px;
            margin: auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 35px;
        }

        h1 {
            color: #a3e6b5;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #b5c5bb;
        }

        .logout-button {
            background: #d9534f;
            color: white;
            border: none;
            padding: 11px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
        }

        .logout-button:hover {
            background: #c43f3b;
        }

        .welcome-card {
            background: #1b2b22;
            border: 1px solid #304b39;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 25px;
        }

        .welcome-card h2 {
            color: #a3e6b5;
            margin-bottom: 12px;
        }

        .welcome-card p {
            color: #c5d2c9;
            line-height: 1.6;
        }

        .section-title {
            color: #a3e6b5;
            margin-bottom: 18px;
        }

        .management-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .management-card {
            background: #1b2b22;
            border: 1px solid #304b39;
            border-radius: 12px;
            padding: 25px;
        }

        .management-card h3 {
            color: #a3e6b5;
            margin-bottom: 12px;
        }

        .management-card p {
            color: #c5d2c9;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .manage-button {
            display: inline-block;
            background: #3d8052;
            color: white;
            text-decoration: none;
            padding: 11px 18px;
            border-radius: 6px;
            font-size: 14px;
            transition: background 0.2s ease;
        }

        .manage-button:hover {
            background: #4c9963;
        }

        .unread-count {
            display: inline-block;
            background: #d9534f;
            color: white;
            padding: 4px 9px;
            border-radius: 20px;
            font-size: 12px;
            margin-left: 5px;
        }

        @media (max-width: 600px) {
            body {
                padding: 20px;
            }

            .welcome-card {
                padding: 22px;
            }
        }
    </style>
</head>

<body>

    <main class="container">

        <header class="header">
            <div>
                <h1>Admin Dashboard</h1>
                <p class="subtitle">Arvin's Portfolio Management</p>
            </div>

            <form action="logout.php" method="POST">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>"
                >

                <button type="submit" class="logout-button">
                    Logout
                </button>
            </form>
        </header>

        <section class="welcome-card">
            <h2>Welcome, <?php echo $username; ?>!</h2>

            <p>
                You have successfully accessed the admin dashboard.
                Use the management sections below to manage your
                portfolio projects and contact messages.
            </p>
        </section>

        <section>
            <h2 class="section-title">Management Sections</h2>

            <div class="management-grid">

                <div class="management-card">
                    <h3>Manage Projects</h3>

                    <p>
                        Add new projects, update existing project details,
                        upload images, and remove projects from your portfolio.
                    </p>

                    <a href="projects.php" class="manage-button">
                        Manage Projects
                    </a>
                </div>

                <div class="management-card">
                    <h3>
                        Manage Messages

                        <?php if ($unreadMessages > 0): ?>
                            <span class="unread-count">
                                <?php echo $unreadMessages; ?> New
                            </span>
                        <?php endif; ?>
                    </h3>

                    <p>
                        View messages submitted through your contact form,
                        mark messages as read, and delete unwanted messages.
                    </p>

                    <a href="messages.php" class="manage-button">
                        View Messages
                    </a>
                </div>

            </div>
        </section>

    </main>

</body>
</html>
