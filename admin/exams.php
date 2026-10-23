<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';
require_admin();

// ---- Handle Add / Edit exam ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_type']) && $_POST['form_type'] === 'exam') {
    $exam_id      = $_POST['exam_id'] ?? '';
    $subject_code = trim($_POST['subject_code'] ?? '');
    $subject_name = trim($_POST['subject_name'] ?? '');
    $exam_date    = $_POST['exam_date'] ?? '';
    $start_time   = $_POST['start_time'] ?? '';
    $end_time     = $_POST['end_time'] ?? '';

    $errors = validate_required([
        'Subject Code' => $subject_code,
        'Subject Name' => $subject_name,
        'Exam Date'    => $exam_date,
        'Start Time'   => $start_time,
        'End Time'     => $end_time,
    ]);
    if (!$errors && $end_time <= $start_time) {
        $errors[] = 'End time must be after start time.';
    }

    if ($errors) {
        set_flash('error', implode(' ', $errors));
        redirect('exams.php');
    }

    if ($exam_id) {
        $stmt = $pdo->prepare('UPDATE exams SET subject_code=?, subject_name=?, exam_date=?, start_time=?, end_time=? WHERE exam_id=?');
        $stmt->execute([$subject_code, $subject_name, $exam_date, $start_time, $end_time, $exam_id]);
        set_flash('success', 'Exam updated successfully.');
    } else {
        $stmt = $pdo->prepare('INSERT INTO exams (subject_code, subject_name, exam_date, start_time, end_time) VALUES (?,?,?,?,?)');
        $stmt->execute([$subject_code, $subject_name, $exam_date, $start_time, $end_time]);
        set_flash('success', 'Exam added successfully.');
    }
    redirect('exams.php');
}

// ---- Handle "Register students for exam" ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_type']) && $_POST['form_type'] === 'register') {
    $exam_id = (int)($_POST['exam_id'] ?? 0);
    $mode    = $_POST['mode'] ?? 'all';

    if ($exam_id <= 0) {
        set_flash('error', 'Please choose a valid exam.');
        redirect('exams.php');
    }

    if ($mode === 'all') {
        // Register every student in the system for this exam
        $studentIds = $pdo->query('SELECT student_id FROM students')->fetchAll(PDO::FETCH_COLUMN);
    } else {
        // Register only students from a chosen department
        $dept = trim($_POST['department'] ?? '');
        $stmt = $pdo->prepare('SELECT student_id FROM students WHERE department = ?');
        $stmt->execute([$dept]);
        $studentIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    $insertStmt = $pdo->prepare('INSERT IGNORE INTO exam_registrations (exam_id, student_id) VALUES (?, ?)');
    $count = 0;
    foreach ($studentIds as $sid) {
        $insertStmt->execute([$exam_id, $sid]);
        $count += $insertStmt->rowCount();
    }
    set_flash('success', "$count student(s) registered for the exam.");
    redirect('exams.php');
}

// ---- List exams with registered-student counts ----
$exams = $pdo->query('
    SELECT e.*, COUNT(er.reg_id) AS registered_count
    FROM exams e
    LEFT JOIN exam_registrations er ON er.exam_id = e.exam_id
    GROUP BY e.exam_id
    ORDER BY e.exam_date DESC
')->fetchAll();

$departments = $pdo->query('SELECT DISTINCT department FROM students ORDER BY department')->fetchAll(PDO::FETCH_COLUMN);

$pageTitle = 'Manage Exams';
$active = 'exams';
require __DIR__ . '/../includes/admin_header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="page-title mb-0">Manage Exams</h3>
  <div class="no-print">
    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#registerModal">
      <i class="bi bi-person-check"></i> Register Students
    </button>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#examModal" onclick="openAddModal()">
      <i class="bi bi-plus-lg"></i> Add Exam
    </button>
  </div>
</div>

<div class="panel">
  <div class="table-responsive">
    <table class="table align-middle">
      <thead>
        <tr>
          <th>Subject Code</th><th>Subject Name</th><th>Date</th><th>Time</th><th>Registered Students</th><th class="no-print">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$exams): ?>
          <tr><td colspan="6" class="text-muted text-center py-3">No exams found.</td></tr>
        <?php endif; ?>
        <?php foreach ($exams as $e): ?>
          <tr>
            <td><?= h($e['subject_code']) ?></td>
            <td><?= h($e['subject_name']) ?></td>
            <td><?= h(date('d M Y', strtotime($e['exam_date']))) ?></td>
            <td><?= h(date('h:i A', strtotime($e['start_time']))) ?> - <?= h(date('h:i A', strtotime($e['end_time']))) ?></td>
            <td><span class="badge bg-secondary"><?= (int)$e['registered_count'] ?></span></td>
            <td class="no-print">
              <button class="btn btn-sm btn-outline-primary" onclick='openEditModal(<?= json_encode($e) ?>)'>
                <i class="bi bi-pencil"></i>
              </button>
              <a href="exam_delete.php?id=<?= (int)$e['exam_id'] ?>" class="btn btn-sm btn-outline-danger confirm-delete">
                <i class="bi bi-trash"></i>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add/Edit Exam Modal -->
<div class="modal fade" id="examModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" class="needs-validation">
        <input type="hidden" name="form_type" value="exam">
        <div class="modal-header">
          <h5 class="modal-title" id="examModalTitle">Add Exam</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="exam_id" id="exam_id">
          <div class="mb-2">
            <label class="form-label">Subject Code</label>
            <input type="text" name="subject_code" id="subject_code" class="form-control" required>
          </div>
          <div class="mb-2">
            <label class="form-label">Subject Name</label>
            <input type="text" name="subject_name" id="subject_name" class="form-control" required>
          </div>
          <div class="mb-2">
            <label class="form-label">Exam Date</label>
            <input type="date" name="exam_date" id="exam_date" class="form-control" required>
          </div>
          <div class="row">
            <div class="col-md-6 mb-2">
              <label class="form-label">Start Time</label>
              <input type="time" name="start_time" id="start_time" class="form-control" required>
            </div>
            <div class="col-md-6 mb-2">
              <label class="form-label">End Time</label>
              <input type="time" name="end_time" id="end_time" class="form-control" required>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Exam</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Register Students Modal -->
<div class="modal fade" id="registerModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <input type="hidden" name="form_type" value="register">
        <div class="modal-header">
          <h5 class="modal-title">Register Students for an Exam</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-2">
            <label class="form-label">Exam</label>
            <select name="exam_id" class="form-select" required>
              <option value="">-- Select Exam --</option>
              <?php foreach ($exams as $e): ?>
                <option value="<?= (int)$e['exam_id'] ?>"><?= h($e['subject_code'] . ' - ' . $e['subject_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-2">
            <label class="form-label">Register</label>
            <select name="mode" id="regMode" class="form-select" onchange="toggleDept()">
              <option value="all">All Students</option>
              <option value="department">Students from one Department</option>
            </select>
          </div>
          <div class="mb-2 d-none" id="deptWrap">
            <label class="form-label">Department</label>
            <select name="department" class="form-select">
              <?php foreach ($departments as $d): ?>
                <option value="<?= h($d) ?>"><?= h($d) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <p class="text-muted small mb-0">Already-registered students are skipped automatically (no duplicates).</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Register</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function openAddModal() {
  document.getElementById('examModalTitle').innerText = 'Add Exam';
  document.getElementById('exam_id').value = '';
  document.getElementById('subject_code').value = '';
  document.getElementById('subject_name').value = '';
  document.getElementById('exam_date').value = '';
  document.getElementById('start_time').value = '';
  document.getElementById('end_time').value = '';
}
function openEditModal(e) {
  document.getElementById('examModalTitle').innerText = 'Edit Exam';
  document.getElementById('exam_id').value = e.exam_id;
  document.getElementById('subject_code').value = e.subject_code;
  document.getElementById('subject_name').value = e.subject_name;
  document.getElementById('exam_date').value = e.exam_date;
  document.getElementById('start_time').value = e.start_time;
  document.getElementById('end_time').value = e.end_time;
  new bootstrap.Modal(document.getElementById('examModal')).show();
}
function toggleDept() {
  document.getElementById('deptWrap').classList.toggle('d-none', document.getElementById('regMode').value !== 'department');
}
</script>

<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
