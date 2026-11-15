<?php
require_once __DIR__ . "/../includes/auth.php";
requireAdmin();

$username = htmlspecialchars(
    $_SESSION['admin_username'] ?? 'Admin',
    ENT_QUOTES,
    'UTF-8'
);
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
        }

        .welcome-card h2 {
            color: #a3e6b5;
            margin-bottom: 12px;
        }

        .welcome-card p {
            color: #c5d2c9;
            line-height: 1.6;
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
                This page will be used to manage your portfolio projects,
                skills, and contact messages.
            </p>
        </section>

    </main>

</body>
</html>
