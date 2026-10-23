<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';
require_student();

$studentId = $_SESSION['student_id'];

$stmt = $pdo->prepare('SELECT * FROM students WHERE student_id = ?');
$stmt->execute([$studentId]);
$student = $stmt->fetch();

// All exams this student is registered for, with allocation details if generated yet
$stmt = $pdo->prepare('
    SELECT e.exam_id, e.subject_code, e.subject_name, e.exam_date, e.start_time, e.end_time,
           a.seat_no, h.hall_number, h.building, h.floor
    FROM exam_registrations er
    JOIN exams e ON e.exam_id = er.exam_id
    LEFT JOIN allocations a ON a.exam_id = e.exam_id AND a.student_id = er.student_id
    LEFT JOIN halls h ON h.hall_id = a.hall_id
    WHERE er.student_id = ?
    ORDER BY e.exam_date ASC
');
$stmt->execute([$studentId]);
$exams = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Exams - Exam Hall Allocation System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="../css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar-app d-flex justify-content-between align-items-center no-print">
  <span class="brand"><i class="bi bi-mortarboard-fill"></i> ExamHall</span>
  <div>
    <span class="me-3">Hi, <?= h($student['name']) ?> (<?= h($student['register_no']) ?>)</span>
    <a href="../student_logout.php" class="btn btn-sm btn-outline-danger">Logout</a>
  </div>
</nav>

<div class="main-content">
  <h3 class="page-title">My Exam Hall Allocations</h3>

  <div class="panel mb-4">
    <div class="row">
      <div class="col-md-3"><strong>Register No:</strong> <?= h($student['register_no']) ?></div>
      <div class="col-md-3"><strong>Department:</strong> <?= h($student['department']) ?></div>
      <div class="col-md-3"><strong>Year:</strong> <?= h($student['year']) ?></div>
      <div class="col-md-3"><strong>Section:</strong> <?= h($student['section']) ?></div>
    </div>
  </div>

  <?php if (!$exams): ?>
    <div class="panel">
      <p class="text-muted mb-0">You are not registered for any exam yet. Please contact the exam admin.</p>
    </div>
  <?php endif; ?>

  <div class="row g-3">
    <?php foreach ($exams as $e): ?>
      <div class="col-md-6">
        <div class="panel h-100">
          <h5><?= h($e['subject_code'] . ' - ' . $e['subject_name']) ?></h5>
          <p class="mb-1"><i class="bi bi-calendar-event"></i> <?= h(date('d M Y (l)', strtotime($e['exam_date']))) ?></p>
          <p class="mb-3"><i class="bi bi-clock"></i> <?= h(date('h:i A', strtotime($e['start_time']))) ?> - <?= h(date('h:i A', strtotime($e['end_time']))) ?></p>

          <?php if ($e['hall_number']): ?>
            <div class="alert alert-success mb-0">
              <div class="row">
                <div class="col-6"><strong>Hall Number</strong><br><span class="fs-4"><?= h($e['hall_number']) ?></span></div>
                <div class="col-6"><strong>Seat Number</strong><br><span class="fs-4"><?= h($e['seat_no']) ?></span></div>
              </div>
              <hr>
              <small><?= h($e['building']) ?>, Floor: <?= h($e['floor']) ?></small>
            </div>
          <?php else: ?>
            <div class="alert alert-warning mb-0">Hall allocation not generated yet. Please check back later.</div>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
