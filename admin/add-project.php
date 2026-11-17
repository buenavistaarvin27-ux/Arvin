<?php
require_once __DIR__ . "/../includes/auth.php";
requireAdmin();

require_once __DIR__ . "/../config/database.php";

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Verify CSRF token
    $token = $_POST['csrf_token'] ?? '';
    $sessionToken = $_SESSION['csrf_token'] ?? '';

    if (
        $sessionToken === '' ||
        $token === '' ||
        !hash_equals($sessionToken, $token)
    ) {
        http_response_code(403);
        exit("Invalid request.");
    }

    // Get form values
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $technologies = trim($_POST['technologies'] ?? '');
    $project_url = trim($_POST['project_url'] ?? '');
    $sort_order = filter_var(
        $_POST['sort_order'] ?? 0,
        FILTER_VALIDATE_INT
    );
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $status = $_POST['status'] ?? 'active';

    // Validate required fields
    if ($title === '' || $description === '') {
        $error = "Project title and description are required.";

    } elseif ($sort_order === false || $sort_order < 0) {
        $error = "Please enter a valid display order.";

    } elseif (!in_array($status, ['active', 'inactive'], true)) {
        $error = "Invalid project status.";

    } elseif (
        $project_url !== '' &&
        !filter_var($project_url, FILTER_VALIDATE_URL)
    ) {
        $error = "Please enter a valid project URL.";

    } else {

        try {
            $stmt = $pdo->prepare("
                INSERT INTO projects (
                    title,
                    description,
                    category,
                    technologies,
                    project_url,
                    sort_order,
                    is_featured,
                    status
                ) VALUES (
                    :title,
                    :description,
                    :category,
                    :technologies,
                    :project_url,
                    :sort_order,
                    :is_featured,
                    :status
                )
            ");

            $stmt->execute([
                ':title' => $title,
                ':description' => $description,
                ':category' => $category !== '' ? $category : null,
                ':technologies' => $technologies !== '' ? $technologies : null,
                ':project_url' => $project_url !== '' ? $project_url : null,
                ':sort_order' => $sort_order,
                ':is_featured' => $is_featured,
                ':status' => $status
            ]);

            header("Location: projects.php?success=added");
            exit;

        } catch (PDOException $e) {
            error_log("Project creation failed: " . $e->getMessage());
            $error = "Unable to save the project. Please try again.";
        }
    }
}

function escapeValue($value) {
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add Project | Admin</title>

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
            max-width: 800px;
            margin: auto;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #a3e6b5;
            text-decoration: none;
        }

        h1 {
            color: #a3e6b5;
            margin-bottom: 10px;
        }

        .subtitle {
            color: #b5c5bb;
            margin-bottom: 25px;
        }

        .form-card {
            background: #1b2b22;
            border: 1px solid #304b39;
            border-radius: 12px;
            padding: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #d4dfd7;
        }

        input, textarea, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #405747;
            border-radius: 6px;
            background: #101b16;
            color: white;
            font-size: 14px;
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        input:focus, textarea:focus, select:focus {
            outline: 2px solid #78c992;
            border-color: #78c992;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .checkbox-group input {
            width: auto;
        }

        .checkbox-group label {
            margin: 0;
        }

        .submit-button {
            background: #78c992;
            color: #102016;
            border: none;
            padding: 13px 22px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }

        .submit-button:hover {
            background: #a3e6b5;
        }

        .error {
            background: #49302d;
            color: #ffb5ae;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .hint {
            color: #9eafa3;
            font-size: 12px;
            margin-top: 6px;
        }

        @media (max-width: 600px) {
            body {
                padding: 15px;
            }

            .form-card {
                padding: 20px;
            }
        }
    </style>
</head>

<body>

<main class="container">

    <a href="projects.php" class="back-link">
        &larr; Back to Projects
    </a>

    <h1>Add New Project</h1>

    <p class="subtitle">
        Enter the information for your portfolio project.
    </p>

    <section class="form-card">

        <?php if ($error !== ''): ?>
            <div class="error">
                <?php echo escapeValue($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <input
                type="hidden"
                name="csrf_token"
                value="<?php echo escapeValue($_SESSION['csrf_token']); ?>"
            >

            <div class="form-group">
                <label for="title">Project Title *</label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    maxlength="200"
                    value="<?php echo escapeValue($_POST['title'] ?? ''); ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="description">Description *</label>
                <textarea
                    id="description"
                    name="description"
                    required
                ><?php echo escapeValue($_POST['description'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label for="category">Category</label>
                <input
                    type="text"
                    id="category"
                    name="category"
                    maxlength="100"
                    value="<?php echo escapeValue($_POST['category'] ?? ''); ?>"
                    placeholder="Example: Web Development"
                >
            </div>

            <div class="form-group">
                <label for="technologies">Technologies Used</label>
                <input
                    type="text"
                    id="technologies"
                    name="technologies"
                    value="<?php echo escapeValue($_POST['technologies'] ?? ''); ?>"
                    placeholder="Example: HTML, CSS, JavaScript, PHP"
                >
            </div>

            <div class="form-group">
                <label for="project_url">Project URL</label>
                <input
                    type="url"
                    id="project_url"
                    name="project_url"
                    value="<?php echo escapeValue($_POST['project_url'] ?? ''); ?>"
                    placeholder="https://example.com"
                >
            </div>

            <div class="form-group">
                <label for="sort_order">Display Order</label>
                <input
                    type="number"
                    id="sort_order"
                    name="sort_order"
                    min="0"
                    value="<?php echo escapeValue($_POST['sort_order'] ?? '0'); ?>"
                >
                <p class="hint">Lower numbers appear first.</p>
            </div>

            <div class="form-group checkbox-group">
                <input
                    type="checkbox"
                    id="is_featured"
                    name="is_featured"
                    value="1"
                    <?php echo isset($_POST['is_featured']) ? 'checked' : ''; ?>
                >
                <label for="is_featured">Mark as Featured Project</label>
            </div>

            <div class="form-group">
                <label for="status">Project Status</label>
                <select id="status" name="status">
                    <option
                        value="active"
                        <?php echo ($_POST['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>
                    >
                        Active
                    </option>

                    <option
                        value="inactive"
                        <?php echo ($_POST['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>
                    >
                        Inactive
                    </option>
                </select>
            </div>

            <button type="submit" class="submit-button">
                Save Project
            </button>

        </form>

    </section>

</main>

</body>
</html>
