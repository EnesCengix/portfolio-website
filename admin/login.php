<?php
// ─── admin/login.php ─────────────────────────────────────────
session_start();

// Already logged in → redirect to dashboard
if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../db.php';

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $stmt = $pdo->prepare('SELECT id, username, password FROM admin_users WHERE username = :u LIMIT 1');
        $stmt->execute([':u' => $username]);
        $admin = $stmt->fetch();

        // password_verify works with hashes created by password_hash()
        if ($admin && password_verify($password, $admin['password'])) {
            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);

            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id']        = $admin['id'];
            $_SESSION['admin_username']  = $admin['username'];

            // Optional "remember me" cookie (7 days)
            if (!empty($_POST['remember'])) {
                setcookie('admin_remember', base64_encode($username), time() + 7 * 24 * 3600, '/', '', false, true);
            }

            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    } else {
        $error = 'Both fields are required.';
    }
}

// Pre-fill username from remember cookie
$remembered = '';
if (!empty($_COOKIE['admin_remember'])) {
    $remembered = htmlspecialchars(base64_decode($_COOKIE['admin_remember']));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — Portfolio</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        body { display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .login-card { padding: 50px 40px; width: 100%; max-width: 420px; text-align: center; }
        .login-card h2 { margin-bottom: 30px; font-size: 2rem; }
        .field { margin-bottom: 20px; text-align: left; }
        .field label { display: block; margin-bottom: 8px; color: #94a3b8; font-size: 0.9rem; }
        .field input {
            width: 100%; padding: 14px; border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.1);
            background: rgba(0,0,0,0.2); color: #fff; font-size: 1rem;
            font-family: inherit; transition: 0.3s;
        }
        .field input:focus { outline: none; border-color: #a18cd1; box-shadow: 0 0 12px rgba(161,140,209,0.3); }
        .remember { display: flex; align-items: center; gap: 8px; margin-bottom: 24px; color: #94a3b8; font-size: 0.9rem; cursor: pointer; }
        .remember input { width: auto; }
        .error-msg { background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.4); color: #f87171; padding: 12px; border-radius: 10px; margin-bottom: 20px; font-size: 0.9rem; }
        .login-card .gradient-btn { width: 100%; font-size: 1rem; padding: 14px; }
        .back-link { display: block; margin-top: 20px; color: #64748b; text-decoration: none; font-size: 0.875rem; transition: 0.3s; }
        .back-link:hover { color: #00f2fe; }
        body.light .field input { background: #f1f5f9; border: 1px solid #cbd5e1; color: #0f172a; }
    </style>
</head>
<body>
<div class="login-card glass-panel">
    <h2 class="gradient-text">⚙ Admin Login</h2>

    <?php if ($error): ?>
        <div class="error-msg"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <div class="field">
            <label for="username">Username</label>
            <input type="text" id="username" name="username"
                   value="<?= $remembered ?>" autocomplete="username" required>
        </div>
        <div class="field">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" autocomplete="current-password" required>
        </div>
        <label class="remember">
            <input type="checkbox" name="remember" <?= $remembered ? 'checked' : '' ?>>
            Remember me for 7 days
        </label>
        <button type="submit" class="gradient-btn">Login →</button>
    </form>
    <a href="../index.html" class="back-link">← Back to Portfolio</a>
</div>
</body>
</html>
