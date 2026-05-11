<?php
// ─── admin/logout.php ─────────────────────────────────────────
session_start();
session_unset();
session_destroy();

// Clear remember-me cookie
if (isset($_COOKIE['admin_remember'])) {
    setcookie('admin_remember', '', time() - 3600, '/', '', false, true);
}

header('Location: login.php');
exit;
