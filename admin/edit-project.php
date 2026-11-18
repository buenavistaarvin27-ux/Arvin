
<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

require_once __DIR__ . '/../config/database.php';

$error = '';
$newImagePath = null;

// Get project ID
$projectId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$projectId || $projectId < 1) {
    header("Location: projects.php");
    exit;
}

// Fetch project
try {
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $projectId]);
    $project = $stmt->fetch();

    if (!$project) {
        header("Location: projects.php?error=not_found");
        exit;
    }
} catch (PDOException $e) {
    error_log("Project fetch error: " . $e->getMessage());
    die("Unable to load project.");
}

// Handle project update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Verify CSRF token
    $submittedToken = $_POST['csrf_token'] ?? '';
    $sessionToken = $_SESSION['csrf_token'] ?? '';

    if (
        !is_string($submittedToken) ||
        !is_string($sessionToken) ||
        $sessionToken === '' ||
        !hash_equals($sessionToken, $submittedToken)
    ) {
        http_response_code(403);
        die("Invalid security token. Please refresh the page.");
    }

    // Get form values
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $technologies = trim($_POST['technologies'] ?? '');
    $projectUrl = trim($_POST['project_url'] ?? '');
    $sortOrder = filter_var($_POST['sort_order'] ?? null, FILTER_VALIDATE_INT);
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $status = $_POST['status'] ?? '';

    // Validate form fields
    if ($title === '' || $description === '') {
        $error = "Title and description are required.";

    } elseif ($sortOrder === false || $sortOrder === null || $sortOrder < 0) {
        $error = "Display order must be a valid non-negative number.";

    } elseif (!in_array($status, ['active', 'inactive'], true)) {
        $error = "Please select a valid project status.";

    } elseif (
        $projectUrl !== '' &&
        (
            !filter_var($projectUrl, FILTER_VALIDATE_URL) ||
            !in_array(
                strtolower(parse_url($projectUrl, PHP_URL_SCHEME) ?? ''),
                ['http', 'https'],
                true
            )
        )
    ) {
        $error = "Please enter a valid HTTP or HTTPS project URL.";
    }

    // Process replacement image if selected
    if (
        $error === '' &&
        isset($_FILES['image']) &&
        $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
    ) {
        $file = $_FILES['image'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error = "Image upload failed. Please try again.";

        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $error = "Image must not exceed 2 MB.";

        } elseif (
            empty($file['tmp_name']) ||
            !is_uploaded_file($file['tmp_name'])
        ) {
            $error = "Invalid image upload.";

        } else {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);

            if ($finfo === false) {
                $error = "Unable to validate the uploaded image.";
            } else {
                $mimeType = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);

                $allowedTypes = [
                    'image/jpeg' => 'jpg',
                    'image/png'  => 'png',
                    'image/webp' => 'webp'
                ];

                $imageInfo = getimagesize($file['tmp_name']);

                if (
                    !isset($allowedTypes[$mimeType]) ||
                    $imageInfo === false ||
                    $imageInfo['mime'] !== $mimeType
                ) {
                    $error = "Only valid JPG, PNG, and WEBP images are allowed.";
                } else {
                    $uploadDirectory = __DIR__ . '/../uploads';

                    if (
                        !is_dir($uploadDirectory) ||
                        !is_writable($uploadDirectory)
                    ) {
                        $error = "The uploads folder is unavailable.";
                    } else {
                        $filename = bin2hex(random_bytes(16)) . '.' .
                            $allowedTypes[$mimeType];

                        $destination = $uploadDirectory . '/' . $filename;

                        if (move_uploaded_file($file['tmp_name'], $destination)) {
                            $newImagePath = 'uploads/' . $filename;
                        } else {
                            $error = "Unable to save the uploaded image.";
                        }
                    }
                }
            }
        }
    }

    // Save changes
    if ($error === '') {

        $oldImagePath = $project['image'] ?? null;
        $imagePath = $newImagePath ?? $oldImagePath;

        try {
            $stmt = $pdo->prepare("
                UPDATE projects SET
                    title = :title,
                    description = :description,
                    category = :category,
                    technologies = :technologies,
                    project_url = :project_url,
                    image = :image,
                    sort_order = :sort_order,
                    is_featured = :is_featured,
                    status = :status
                WHERE id = :id
            ");

            $stmt->execute([
                'title' => $title,
                'description' => $description,
                'category' => $category,
                'technologies' => $technologies,
                'project_url' => $projectUrl !== '' ? $projectUrl : null,
                'image' => $imagePath,
                'sort_order' => $sortOrder,
                'is_featured' => $isFeatured,
                'status' => $status,
                'id' => $projectId
            ]);

            // Delete old image only after successful replacement
            if ($newImagePath !== null && !empty($oldImagePath)) {
                if (
                    preg_match(
                        '#^uploads/[a-f0-9]{32}\.(jpg|png|webp)$#',
                        $oldImagePath
                    )
                ) {
                    $oldFile = __DIR__ . '/../' . $oldImagePath;

                    if (is_file($oldFile)) {
                        unlink($oldFile);
                    }
                }
            }

            header("Location: projects.php?success=updated");
            exit;

        } catch (PDOException $e) {
            error_log("Project update error: " . $e->getMessage());

            // Remove newly uploaded image if database update fails
            if ($newImagePath !== null) {
                $newFile = __DIR__ . '/../' . $newImagePath;

                if (is_file($newFile)) {
                    unlink($newFile);
                }
            }

            $error = "Unable to update project. Please try again.";
        }
    }

    // Preserve submitted values
    $project = array_merge($project, [
        'title' => $title,
        'description' => $description,
        'category' => $category,
        'technologies' => $technologies,
        'project_url' => $projectUrl,
        'sort_order' => $sortOrder,
        'is_featured' => $isFeatured,
        'status' => $status
    ]);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Project | Admin</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 30px 20px;
            font-family: Arial, sans-serif;
            background: #101b17;
            color: #e8f0eb;
        }

        .container {
            max-width: 750px;
            margin: 0 auto;
        }

        .form-card {
            background: #192821;
            border: 1px solid #294538;
            border-radius: 12px;
            padding: 30px;
        }

        h1 {
            margin-top: 0;
            color: #8bc9a5;
        }

        .description {
            color: #a9bcb0;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        input,
        textarea,
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #395746;
            border-radius: 7px;
            background: #101b17;
            color: #ffffff;
            font-size: 14px;
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        input[type="file"] {
            padding: 10px;
        }

        .current-image {
            width: 180px;
            max-height: 140px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #395746;
            display: block;
            margin-bottom: 12px;
        }

        .image-hint {
            color: #a9bcb0;
            font-size: 13px;
            margin-top: 8px;
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

        .error {
            padding: 12px;
            margin-bottom: 20px;
            background: #492522;
            color: #ffb5ac;
            border-radius: 7px;
        }

        .buttons {
            display: flex;
            gap: 12px;
            margin-top: 25px;
        }

        button,
        .cancel-btn {
            padding: 12px 20px;
            border: none;
            border-radius: 7px;
            font-weight: bold;
            text-decoration: none;
            cursor: pointer;
        }

        button {
            background: #8bc9a5;
            color: #102219;
        }

        .cancel-btn {
            background: #34483c;
            color: #ffffff;
        }

        button:hover {
            background: #a2dbb8;
        }

        @media (max-width: 600px) {
            .form-card {
                padding: 20px;
            }

            .buttons {
                flex-wrap: wrap;
            }
        }
    </style>
</head>

<body>

<div class="container">
    <div class="form-card">

        <h1>Edit Project</h1>
        <p class="description">Update your portfolio project information.</p>

        <?php if (!empty($error)): ?>
            <div class="error">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
            >

            <div class="form-group">
                <label for="title">Project Title *</label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    value="<?= htmlspecialchars($project['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="description">Description *</label>
                <textarea
                    id="description"
                    name="description"
                    required
                ><?= htmlspecialchars($project['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>

            <div class="form-group">
                <label for="category">Category</label>
                <input
                    type="text"
                    id="category"
                    name="category"
                    value="<?= htmlspecialchars($project['category'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                >
            </div>

            <div class="form-group">
                <label for="technologies">Technologies</label>
                <input
                    type="text"
                    id="technologies"
                    name="technologies"
                    value="<?= htmlspecialchars($project['technologies'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                >
            </div>

            <div class="form-group">
                <label for="project_url">Project URL</label>
                <input
                    type="url"
                    id="project_url"
                    name="project_url"
                    value="<?= htmlspecialchars($project['project_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                >
            </div>

            <div class="form-group">
                <label>Current Project Image</label>

                <?php if (!empty($project['image'])): ?>
                    <img
                        src="../<?= htmlspecialchars($project['image'], ENT_QUOTES, 'UTF-8') ?>"
                        alt="Current project image"
                        class="current-image"
                    >
                <?php else: ?>
                    <p class="image-hint">No image uploaded yet.</p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="image">Replace Project Image</label>
                <input
                    type="file"
                    id="image"
                    name="image"
                    accept=".jpg,.jpeg,.png,.webp"
                >

                <p class="image-hint">
                    Optional. JPG, PNG, or WEBP. Maximum size: 2 MB.
                    Leave empty to keep the current image.
                </p>
            </div>

            <div class="form-group">
                <label for="sort_order">Display Order</label>
                <input
                    type="number"
                    id="sort_order"
                    name="sort_order"
                    min="0"
                    value="<?= htmlspecialchars((string)($project['sort_order'] ?? 0), ENT_QUOTES, 'UTF-8') ?>"
                    required
                >
            </div>

            <div class="form-group checkbox-group">
                <input
                    type="checkbox"
                    id="is_featured"
                    name="is_featured"
                    <?= !empty($project['is_featured']) ? 'checked' : '' ?>
                >
                <label for="is_featured">Featured Project</label>
            </div>

            <div class="form-group">
                <label for="status">Project Status</label>

                <select id="status" name="status" required>
                    <option
                        value="active"
                        <?= ($project['status'] ?? '') === 'active' ? 'selected' : '' ?>
                    >
                        Active
                    </option>

                    <option
                        value="inactive"
                        <?= ($project['status'] ?? '') === 'inactive' ? 'selected' : '' ?>
                    >
                        Inactive
                    </option>
                </select>
            </div>

            <div class="buttons">
                <button type="submit">Update Project</button>
                <a href="projects.php" class="cancel-btn">Cancel</a>
            </div>

        </form>
    </div>
</div>

</body>
</html>
