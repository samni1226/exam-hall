<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';
require_admin();

$totalStudents = $pdo->query('SELECT COUNT(*) FROM students')->fetchColumn();
$totalExams    = $pdo->query('SELECT COUNT(*) FROM exams')->fetchColumn();
$totalHalls    = $pdo->query('SELECT COUNT(*) FROM halls')->fetchColumn();
$totalCapacity = $pdo->query('SELECT COALESCE(SUM(capacity),0) FROM halls')->fetchColumn();
$totalAllocated = $pdo->query('SELECT COUNT(*) FROM allocations')->fetchColumn();
$availableSeats = max(0, $totalCapacity - $totalAllocated);

$recentExams = $pdo->query('SELECT exam_id, subject_code, subject_name, exam_date, start_time, end_time FROM exams ORDER BY exam_date DESC LIMIT 5')->fetchAll();

$pageTitle = 'Dashboard';
$active = 'dashboard';
require __DIR__ . '/../includes/admin_header.php';
?>

<h3 class="page-title">Welcome, <?= h($_SESSION['admin_name']) ?> 👋</h3>

<div class="row g-3 mb-4">
  <div class="col-md-3 col-6">
    <div class="stat-card">
      <div class="stat-value"><?= (int)$totalStudents ?></div>
      <div class="stat-label">Total Students</div>
    </div>
  </div>
  <div class="col-md-3 col-6">
    <div class="stat-card">
      <div class="stat-value"><?= (int)$totalExams ?></div>
      <div class="stat-label">Total Exams</div>
    </div>
  </div>
  <div class="col-md-3 col-6">
    <div class="stat-card">
      <div class="stat-value"><?= (int)$totalHalls ?></div>
      <div class="stat-label">Total Halls</div>
    </div>
  </div>
  <div class="col-md-3 col-6">
    <div class="stat-card">
      <div class="stat-value"><?= (int)$availableSeats ?> / <?= (int)$totalCapacity ?></div>
      <div class="stat-label">Available Seats</div>
    </div>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-3 col-6">
    <a href="allocate.php" class="text-decoration-none">
      <div class="panel text-center py-4">
        <i class="bi bi-shuffle fs-2 text-primary"></i>
        <p class="mt-2 mb-0 fw-semibold text-dark">Generate Allocation</p>
      </div>
    </a>
  </div>
  <div class="col-md-3 col-6">
    <a href="report.php" class="text-decoration-none">
      <div class="panel text-center py-4">
        <i class="bi bi-printer fs-2 text-primary"></i>
        <p class="mt-2 mb-0 fw-semibold text-dark">View Allocation</p>
      </div>
    </a>
  </div>
  <div class="col-md-3 col-6">
    <a href="students.php" class="text-decoration-none">
      <div class="panel text-center py-4">
        <i class="bi bi-people-fill fs-2 text-primary"></i>
        <p class="mt-2 mb-0 fw-semibold text-dark">Manage Students</p>
      </div>
    </a>
  </div>
  <div class="col-md-3 col-6">
    <a href="exams.php" class="text-decoration-none">
      <div class="panel text-center py-4">
        <i class="bi bi-journal-text fs-2 text-primary"></i>
        <p class="mt-2 mb-0 fw-semibold text-dark">Manage Exams</p>
      </div>
    </a>
  </div>
</div>

<div class="panel">
  <h5 class="mb-3">Upcoming / Recent Exams</h5>
  <div class="table-responsive">
    <table class="table align-middle">
      <thead>
        <tr><th>Subject Code</th><th>Subject Name</th><th>Date</th><th>Time</th></tr>
      </thead>
      <tbody>
        <?php if (!$recentExams): ?>
          <tr><td colspan="4" class="text-muted text-center py-3">No exams added yet. <a href="exams.php">Add one</a>.</td></tr>
        <?php endif; ?>
        <?php foreach ($recentExams as $e): ?>
          <tr>
            <td><?= h($e['subject_code']) ?></td>
            <td><?= h($e['subject_name']) ?></td>
            <td><?= h(date('d M Y', strtotime($e['exam_date']))) ?></td>
            <td><?= h(date('h:i A', strtotime($e['start_time']))) ?> - <?= h(date('h:i A', strtotime($e['end_time']))) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
