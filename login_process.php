<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('login.php');
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    set_flash('error', 'Please enter both username and password.');
    redirect('login.php');
}

$stmt = $pdo->prepare('SELECT admin_id, username, password, full_name FROM admins WHERE username = ? LIMIT 1');
$stmt->execute([$username]);
$admin = $stmt->fetch();

if ($admin && password_verify($password, $admin['password'])) {
    // Regenerate session id to prevent session fixation
    session_regenerate_id(true);
    $_SESSION['admin_id']   = $admin['admin_id'];
    $_SESSION['admin_name'] = $admin['full_name'];
    redirect('admin/dashboard.php');
} else {
    set_flash('error', 'Invalid username or password.');
    redirect('login.php');
}
