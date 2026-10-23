<?php
require_once __DIR__ . '/includes/functions.php';
if (!empty($_SESSION['student_id'])) {
    redirect('student/dashboard.php');
}
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Login - Exam Hall Allocation System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card">
    <h2><i class="bi bi-person-badge-fill text-primary"></i> ExamHall</h2>
    <p class="subtitle">Student login &mdash; View your exam hall &amp; seat</p>

    <?php if ($flash): ?>
      <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?>"><?= h($flash['message']) ?></div>
    <?php endif; ?>

    <form action="student_login_process.php" method="POST" class="needs-validation">
      <div class="mb-3">
        <label class="form-label">Register Number</label>
        <input type="text" name="register_no" class="form-control" required autofocus placeholder="e.g. CS2021001">
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>
    <p class="text-center mt-3 mb-0"><small>Admin? <a href="login.php">Login here</a></small></p>
    <p class="text-center mt-2"><small class="text-muted">Demo: CS2021001 / CS2021001</small></p>
  </div>
</div>
</body>
</html>
