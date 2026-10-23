<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';
require_admin();

// ---- Handle Add / Edit submission ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'] ?? '';
    $register_no = trim($_POST['register_no'] ?? '');
    $name        = trim($_POST['name'] ?? '');
    $department  = trim($_POST['department'] ?? '');
    $year        = trim($_POST['year'] ?? '');
    $section     = trim($_POST['section'] ?? '');

    $errors = validate_required([
        'Register Number' => $register_no,
        'Student Name'    => $name,
        'Department'      => $department,
        'Year'            => $year,
        'Section'         => $section,
    ]);

    if ($errors) {
        set_flash('error', implode(' ', $errors));
        redirect('students.php');
    }

    try {
        if ($student_id) {
            // Edit existing student
            $stmt = $pdo->prepare('UPDATE students SET register_no=?, name=?, department=?, year=?, section=? WHERE student_id=?');
            $stmt->execute([$register_no, $name, $department, $year, $section, $student_id]);
            set_flash('success', 'Student updated successfully.');
        } else {
            // New student - default password = register number (hashed)
            $hashedPassword = password_hash($register_no, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO students (register_no, name, department, year, section, password) VALUES (?,?,?,?,?,?)');
            $stmt->execute([$register_no, $name, $department, $year, $section, $hashedPassword]);
            set_flash('success', 'Student added successfully. Default password is their register number.');
        }
    } catch (PDOException $e) {
        // Likely a duplicate register_no (UNIQUE constraint)
        set_flash('error', 'Could not save student. Register number might already exist.');
    }
    redirect('students.php');
}

// ---- Search / filter ----
$search = trim($_GET['search'] ?? '');
$deptFilter = trim($_GET['department'] ?? '');

$sql = 'SELECT * FROM students WHERE 1=1';
$params = [];
if ($search !== '') {
    $sql .= ' AND (register_no LIKE ? OR name LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($deptFilter !== '') {
    $sql .= ' AND department = ?';
    $params[] = $deptFilter;
}
$sql .= ' ORDER BY student_id DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll();

$departments = $pdo->query('SELECT DISTINCT department FROM students ORDER BY department')->fetchAll(PDO::FETCH_COLUMN);

$pageTitle = 'Manage Students';
$active = 'students';
require __DIR__ . '/../includes/admin_header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="page-title mb-0">Manage Students</h3>
  <button class="btn btn-primary no-print" data-bs-toggle="modal" data-bs-target="#studentModal" onclick="openAddModal()">
    <i class="bi bi-plus-lg"></i> Add Student
  </button>
</div>

<div class="panel no-print">
  <form class="row g-2" method="GET">
    <div class="col-md-4">
      <input type="text" name="search" class="form-control" placeholder="Search by Register No / Name" value="<?= h($search) ?>">
    </div>
    <div class="col-md-3">
      <select name="department" class="form-select">
        <option value="">All Departments</option>
        <?php foreach ($departments as $d): ?>
          <option value="<?= h($d) ?>" <?= $deptFilter === $d ? 'selected' : '' ?>><?= h($d) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <button class="btn btn-outline-primary w-100" type="submit"><i class="bi bi-search"></i> Filter</button>
    </div>
    <div class="col-md-2">
      <a href="students.php" class="btn btn-outline-secondary w-100">Reset</a>
    </div>
  </form>
</div>

<div class="panel">
  <div class="table-responsive">
    <table class="table align-middle">
      <thead>
        <tr>
          <th>Register No</th><th>Name</th><th>Department</th><th>Year</th><th>Section</th><th class="no-print">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$students): ?>
          <tr><td colspan="6" class="text-muted text-center py-3">No students found.</td></tr>
        <?php endif; ?>
        <?php foreach ($students as $s): ?>
          <tr>
            <td><?= h($s['register_no']) ?></td>
            <td><?= h($s['name']) ?></td>
            <td><span class="badge badge-dept"><?= h($s['department']) ?></span></td>
            <td><?= h($s['year']) ?></td>
            <td><?= h($s['section']) ?></td>
            <td class="no-print">
              <button class="btn btn-sm btn-outline-primary"
                onclick='openEditModal(<?= json_encode($s) ?>)'>
                <i class="bi bi-pencil"></i>
              </button>
              <a href="student_delete.php?id=<?= (int)$s['student_id'] ?>" class="btn btn-sm btn-outline-danger confirm-delete">
                <i class="bi bi-trash"></i>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="studentModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" class="needs-validation">
        <div class="modal-header">
          <h5 class="modal-title" id="studentModalTitle">Add Student</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="student_id" id="student_id">
          <div class="mb-2">
            <label class="form-label">Register Number</label>
            <input type="text" name="register_no" id="register_no" class="form-control" required>
          </div>
          <div class="mb-2">
            <label class="form-label">Student Name</label>
            <input type="text" name="name" id="name" class="form-control" required>
          </div>
          <div class="row">
            <div class="col-md-4 mb-2">
              <label class="form-label">Department</label>
              <input type="text" name="department" id="department" class="form-control" required>
            </div>
            <div class="col-md-4 mb-2">
              <label class="form-label">Year</label>
              <input type="text" name="year" id="year" class="form-control" required>
            </div>
            <div class="col-md-4 mb-2">
              <label class="form-label">Section</label>
              <input type="text" name="section" id="section" class="form-control" required>
            </div>
          </div>
          <p class="text-muted small mb-0">New students get a default password equal to their register number.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Student</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function openAddModal() {
  document.getElementById('studentModalTitle').innerText = 'Add Student';
  document.getElementById('student_id').value = '';
  document.getElementById('register_no').value = '';
  document.getElementById('name').value = '';
  document.getElementById('department').value = '';
  document.getElementById('year').value = '';
  document.getElementById('section').value = '';
}
function openEditModal(s) {
  document.getElementById('studentModalTitle').innerText = 'Edit Student';
  document.getElementById('student_id').value = s.student_id;
  document.getElementById('register_no').value = s.register_no;
  document.getElementById('name').value = s.name;
  document.getElementById('department').value = s.department;
  document.getElementById('year').value = s.year;
  document.getElementById('section').value = s.section;
  new bootstrap.Modal(document.getElementById('studentModal')).show();
}
</script>

<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
