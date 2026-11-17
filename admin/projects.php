<?php
require_once __DIR__ . "/../includes/auth.php";
requireAdmin();

require_once __DIR__ . "/../config/database.php";

// Get all projects from the database
try {
    $stmt = $pdo->query("
        SELECT *
        FROM projects
        ORDER BY sort_order ASC, created_at DESC
    ");

    $projects = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Projects loading failed: " . $e->getMessage());
    $projects = [];
    $databaseError = true;
}

// Safely display database values
function escape($value) {
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

$username = escape($_SESSION['admin_username'] ?? 'Admin');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Projects | Admin</title>

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
            min-width: 750px;
        }

        th, td {
            padding: 14px;
            text-align: left;
            border-bottom: 1px solid #304b39;
        }

        th {
            color: #a3e6b5;
            background: #24382b;
        }

        td {
            color: #d4dfd7;
        }

        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
        }

        .active {
            background: #244b32;
            color: #a3e6b5;
        }

        .inactive {
            background: #49302d;
            color: #ffb5ae;
        }

        .featured {
            color: #f5d98a;
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
            <h1>Projects Management</h1>
            <p class="subtitle">
                Welcome, <?php echo $username; ?>.
                Manage your portfolio projects here.
            </p>
        </div>
    </header>

    <section class="card">

        <h2>All Projects</h2>

        <?php if (!empty($databaseError)): ?>

            <p class="error-message">
                Unable to load projects. Please check your database connection.
            </p>

        <?php elseif (count($projects) > 0): ?>

            <div class="table-wrapper">

                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Project Title</th>
                            <th>Category</th>
                            <th>Technologies</th>
                            <th>Featured</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($projects as $project): ?>

                            <tr>
                                <td><?php echo escape($project['id']); ?></td>

                                <td>
                                    <?php echo escape($project['title']); ?>
                                </td>

                                <td>
                                    <?php echo escape($project['category']); ?>
                                </td>

                                <td>
                                    <?php echo escape($project['technologies']); ?>
                                </td>

                                <td class="featured">
                                    <?php echo !empty($project['is_featured']) ? 'Yes' : 'No'; ?>
                                </td>

                                <td>
                                    <span class="status <?php echo escape($project['status']); ?>">
                                        <?php echo escape(ucfirst($project['status'])); ?>
                                    </span>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    </tbody>
                </table>

            </div>

        <?php else: ?>

            <div class="empty-message">
                <h3>No Projects Found</h3>
                <p>Your portfolio projects will appear here.</p>
            </div>

        <?php endif; ?>

    </section>

</main>

</body>
</html>
