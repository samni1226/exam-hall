<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';
require_admin();

$exams = $pdo->query('SELECT exam_id, subject_code, subject_name, exam_date FROM exams ORDER BY exam_date DESC')->fetchAll();
$halls = $pdo->query('SELECT * FROM halls ORDER BY hall_number ASC')->fetchAll();
$totalHallCapacity = array_sum(array_column($halls, 'capacity'));

$selectedExamId = (int)($_GET['exam_id'] ?? $_POST['exam_id'] ?? 0);
$registeredCount = 0;
$alreadyAllocatedCount = 0;

if ($selectedExamId) {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM exam_registrations WHERE exam_id = ?');
    $stmt->execute([$selectedExamId]);
    $registeredCount = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM allocations WHERE exam_id = ?');
    $stmt->execute([$selectedExamId]);
    $alreadyAllocatedCount = (int)$stmt->fetchColumn();
}

/* =========================================================
   THE ALLOCATION ALGORITHM
   ---------------------------------------------------------
   1. Get every student registered for the chosen exam.
   2. Get every hall, sorted by hall number, with its capacity.
   3. Walk through the student list in order. For each student,
      put them in the "current" hall until that hall is full,
      then move on to the next hall.
   4. Each seat gets a seat number like "H101-01", "H101-02"...
   5. Before inserting, DELETE any previous allocation for this
      exam so the admin can safely "regenerate" the allocation.
   6. The whole thing runs inside a DB transaction + the table's
      UNIQUE(exam_id, student_id) constraint guarantees a student
      is never allocated twice for the same exam.
   ========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate'])) {
    $examId = (int)$_POST['exam_id'];

    if ($examId <= 0) {
        set_flash('error', 'Please select an exam first.');
        redirect('allocate.php');
    }

    // Step 1: students registered for this exam (ordered so allocation is reproducible)
    $stmt = $pdo->prepare('
        SELECT s.student_id, s.register_no, s.name
        FROM exam_registrations er
        JOIN students s ON s.student_id = er.student_id
        WHERE er.exam_id = ?
        ORDER BY s.register_no ASC
    ');
    $stmt->execute([$examId]);
    $students = $stmt->fetchAll();

    // Step 2: all halls, ordered, with capacity
    $stmt = $pdo->query('SELECT hall_id, hall_number, capacity FROM halls ORDER BY hall_number ASC');
    $halls = $stmt->fetchAll();

    if (!$students) {
        set_flash('error', 'No students are registered for this exam yet. Register students first.');
        redirect('allocate.php?exam_id=' . $examId);
    }
    if (!$halls) {
        set_flash('error', 'No halls available. Please add halls first.');
        redirect('allocate.php?exam_id=' . $examId);
    }

    $totalCapacity = array_sum(array_column($halls, 'capacity'));
    $totalStudents = count($students);

    // Step 8: not enough seats overall -> stop with a clear error, allocate nothing
    if ($totalCapacity < $totalStudents) {
        set_flash('error', "Cannot allocate: {$totalStudents} students need seats but halls only provide {$totalCapacity} seats. Add more halls or increase capacity.");
        redirect('allocate.php?exam_id=' . $examId);
    }

    try {
        $pdo->beginTransaction();

        // Step 5 / Step 9: clear any earlier allocation for this exam so we can regenerate cleanly
        $del = $pdo->prepare('DELETE FROM allocations WHERE exam_id = ?');
        $del->execute([$examId]);

        $insert = $pdo->prepare('INSERT INTO allocations (exam_id, student_id, hall_id, seat_no) VALUES (?, ?, ?, ?)');

        // Step 3 & 4 & 6 & 7: sequentially fill halls, one seat per student, no repeats, no overflow
        $studentIndex = 0;
        foreach ($halls as $hall) {
            $seatCounter = 1;
            while ($seatCounter <= $hall['capacity'] && $studentIndex < $totalStudents) {
                $student = $students[$studentIndex];
                $seatNo = $hall['hall_number'] . '-' . str_pad($seatCounter, 2, '0', STR_PAD_LEFT);

                $insert->execute([$examId, $student['student_id'], $hall['hall_id'], $seatNo]);

                $seatCounter++;
                $studentIndex++;
            }
            if ($studentIndex >= $totalStudents) {
                break; // every student has a seat, remaining halls stay untouched
            }
        }

        // Count how many distinct halls actually ended up with students, just for the confirmation message
        $usedHalls = $pdo->prepare('SELECT COUNT(DISTINCT hall_id) FROM allocations WHERE exam_id = ?');
        $usedHalls->execute([$examId]);
        $hallsUsedCount = (int)$usedHalls->fetchColumn();

        $pdo->commit();
        set_flash('success', "Allocation generated successfully for {$totalStudents} student(s) across {$hallsUsedCount} hall(s).");
    } catch (Exception $e) {
        $pdo->rollBack();
        set_flash('error', 'Allocation failed: ' . $e->getMessage());
    }

    redirect('allocate.php?exam_id=' . $examId);
}

$pageTitle = 'Generate Allocation';
$active = 'allocate';
require __DIR__ . '/../includes/admin_header.php';
?>

<h3 class="page-title">Generate Automatic Hall Allocation</h3>

<div class="panel">
  <form method="GET" class="row g-2 align-items-end">
    <div class="col-md-6">
      <label class="form-label">Select Exam</label>
      <select name="exam_id" class="form-select" onchange="this.form.submit()">
        <option value="">-- Choose an exam --</option>
        <?php foreach ($exams as $e): ?>
          <option value="<?= (int)$e['exam_id'] ?>" <?= $selectedExamId === (int)$e['exam_id'] ? 'selected' : '' ?>>
            <?= h($e['subject_code'] . ' - ' . $e['subject_name'] . ' (' . date('d M Y', strtotime($e['exam_date'])) . ')') ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
  </form>
</div>

<?php if ($selectedExamId): ?>
  <div class="row g-3 mb-3">
    <div class="col-md-4">
      <div class="stat-card">
        <div class="stat-value"><?= $registeredCount ?></div>
        <div class="stat-label">Registered Students</div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="stat-card">
        <div class="stat-value"><?= $totalHallCapacity ?></div>
        <div class="stat-label">Total Hall Capacity</div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="stat-card">
        <div class="stat-value"><?= $alreadyAllocatedCount ?></div>
        <div class="stat-label">Already Allocated</div>
      </div>
    </div>
  </div>

  <div class="panel">
    <?php if ($registeredCount === 0): ?>
      <div class="alert alert-warning mb-0">No students are registered for this exam yet. Go to <a href="exams.php">Manage Exams</a> and use "Register Students".</div>
    <?php elseif ($totalHallCapacity < $registeredCount): ?>
      <div class="alert alert-danger mb-0">
        Insufficient hall capacity: <?= $registeredCount ?> students need seats, but halls only provide <?= $totalHallCapacity ?>.
        Please add more halls or increase capacity in <a href="halls.php">Manage Halls</a>.
      </div>
    <?php else: ?>
      <p>This exam has <strong><?= $registeredCount ?></strong> registered student(s) and <strong><?= $totalHallCapacity ?></strong> total seats available.</p>
      <?php if ($alreadyAllocatedCount > 0): ?>
        <p class="text-muted">An allocation already exists for this exam. Generating again will <strong>replace</strong> it.</p>
      <?php endif; ?>
      <form method="POST">
        <input type="hidden" name="exam_id" value="<?= $selectedExamId ?>">
        <button type="submit" name="generate" class="btn btn-primary">
          <i class="bi bi-shuffle"></i> <?= $alreadyAllocatedCount > 0 ? 'Regenerate Allocation' : 'Generate Allocation' ?>
        </button>
        <a href="report.php?exam_id=<?= $selectedExamId ?>" class="btn btn-outline-secondary">View Report</a>
      </form>
    <?php endif; ?>
  </div>

  <div class="panel">
    <h6>Halls used for this allocation (in order)</h6>
    <table class="table">
      <thead><tr><th>Hall Number</th><th>Building</th><th>Capacity</th></tr></thead>
      <tbody>
        <?php foreach ($halls as $h): ?>
          <tr><td><?= h($h['hall_number']) ?></td><td><?= h($h['building']) ?></td><td><?= (int)$h['capacity'] ?></td></tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
