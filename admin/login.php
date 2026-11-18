<?php

require_once __DIR__ . "/../includes/auth.php";

// Redirect if already logged in
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Login | Arvin Portfolio</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #101713;
            color: white;
            font-family: Arial, sans-serif;
        }

        .login-container {
            width: 90%;
            max-width: 400px;
            padding: 35px;
            background: #1b2720;
            border: 1px solid #354b3b;
            border-radius: 15px;
        }

        h2 {
            text-align: center;
            margin-bottom: 10px;
        }

        p {
            text-align: center;
            color: #aab8ad;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin: 15px 0 8px;
        }

        input {
            width: 100%;
            padding: 13px;
            border-radius: 7px;
            border: 1px solid #405748;
            background: #101713;
            color: white;
        }

        button {
            width: 100%;
            padding: 13px;
            margin-top: 22px;
            background: #65c987;
            border: none;
            border-radius: 7px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background: #4fb875;
        }

        .error {
            color: #ff8585;
            text-align: center;
            font-size: 14px;
        }
    </style>

</head>

<body>

    <div class="login-container">

        <h2>Admin Login</h2>

        <p>Arvin's Personal Portfolio</p>

        <?php if (isset($_GET['error'])): ?>
            <div class="error">
                Invalid email or password.
            </div>
        <?php endif; ?>

        <form action="authenticate.php" method="POST">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"
            >

            <label>Email Address</label>

            <input
                type="email"
                name="email"
                placeholder="Enter your email"
                autocomplete="username"
                required
            >

            <label>Password</label>

            <input
                type="password"
                name="password"
                placeholder="Enter your password"
                autocomplete="current-password"
                required
            >

            <button type="submit">Login</button>

        </form>

    </div>

</body>
</html>
