<?php
// Expects $active to be set by the including page (e.g. 'dashboard', 'students', ...)
$active = $active ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? h($pageTitle) . ' - ' : '' ?>Exam Hall Allocation | Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="../css/style.css" rel="stylesheet">
</head>
<body>
<div class="app-layout">
  <div class="sidebar no-print">
    <span class="brand"><i class="bi bi-mortarboard-fill"></i> ExamHall Admin</span>
    <a href="dashboard.php" class="<?= $active === 'dashboard' ? 'active' : '' ?>"><i class="bi bi-speedometer2"></i>&nbsp; Dashboard</a>
    <a href="students.php" class="<?= $active === 'students' ? 'active' : '' ?>"><i class="bi bi-people-fill"></i>&nbsp; Manage Students</a>
    <a href="exams.php" class="<?= $active === 'exams' ? 'active' : '' ?>"><i class="bi bi-journal-text"></i>&nbsp; Manage Exams</a>
    <a href="halls.php" class="<?= $active === 'halls' ? 'active' : '' ?>"><i class="bi bi-building"></i>&nbsp; Manage Halls</a>
    <a href="allocate.php" class="<?= $active === 'allocate' ? 'active' : '' ?>"><i class="bi bi-shuffle"></i>&nbsp; Generate Allocation</a>
    <a href="report.php" class="<?= $active === 'report' ? 'active' : '' ?>"><i class="bi bi-printer"></i>&nbsp; Allocation Report</a>
    <a href="../logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i>&nbsp; Logout</a>
  </div>
  <div class="main-content">
    <?php $flash = get_flash(); ?>
    <?php if ($flash): ?>
      <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-auto-hide no-print">
        <?= h($flash['message']) ?>
      </div>
    <?php endif; ?>
