<?php
require_once __DIR__ . '/includes/functions.php';
if (!empty($_SESSION['admin_id'])) {
    redirect('admin/dashboard.php');
}
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login - Exam Hall Allocation System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card">
    <h2><i class="bi bi-mortarboard-fill text-primary"></i> ExamHall</h2>
    <p class="subtitle">Admin login &mdash; Exam Hall Allocation System</p>

    <?php if ($flash): ?>
      <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?>"><?= h($flash['message']) ?></div>
    <?php endif; ?>

    <form action="login_process.php" method="POST" class="needs-validation">
      <div class="mb-3">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-control" required autofocus>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>
    <p class="text-center mt-3 mb-0"><small>Are you a student? <a href="student_login.php">Login here</a></small></p>
    <p class="text-center mt-2"><small class="text-muted">Demo: admin / admin123</small></p>
  </div>
</div>
</body>
</html>
