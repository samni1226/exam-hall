<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';
require_admin();

$exams = $pdo->query('SELECT exam_id, subject_code, subject_name, exam_date FROM exams ORDER BY exam_date DESC')->fetchAll();
$departments = $pdo->query('SELECT DISTINCT department FROM students ORDER BY department')->fetchAll(PDO::FETCH_COLUMN);
$halls = $pdo->query('SELECT hall_id, hall_number FROM halls ORDER BY hall_number')->fetchAll();

$examFilter = trim($_GET['exam_id'] ?? '');
$deptFilter = trim($_GET['department'] ?? '');
$hallFilter = trim($_GET['hall_id'] ?? '');
$regSearch  = trim($_GET['search'] ?? '');

$sql = "
  SELECT a.seat_no, s.register_no, s.name, s.department, s.year, s.section,
         e.subject_code, e.subject_name, e.exam_date,
         h.hall_number, h.building
  FROM allocations a
  JOIN students s ON s.student_id = a.student_id
  JOIN exams e    ON e.exam_id = a.exam_id
  JOIN halls h    ON h.hall_id = a.hall_id
  WHERE 1=1
";
$params = [];

if ($examFilter !== '') {
    $sql .= ' AND a.exam_id = ?';
    $params[] = $examFilter;
}
if ($deptFilter !== '') {
    $sql .= ' AND s.department = ?';
    $params[] = $deptFilter;
}
if ($hallFilter !== '') {
    $sql .= ' AND a.hall_id = ?';
    $params[] = $hallFilter;
}
if ($regSearch !== '') {
    $sql .= ' AND (s.register_no LIKE ? OR h.hall_number LIKE ?)';
    $params[] = "%$regSearch%";
    $params[] = "%$regSearch%";
}
$sql .= ' ORDER BY h.hall_number ASC, a.seat_no ASC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$pageTitle = 'Allocation Report';
$active = 'report';
require __DIR__ . '/../includes/admin_header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3 no-print">
  <h3 class="page-title mb-0">Allocation Report</h3>
  <div>
    <button onclick="window.print()" class="btn btn-outline-secondary"><i class="bi bi-printer"></i> Print</button>
    <a href="report_export.php?<?= h(http_build_query($_GET)) ?>" class="btn btn-primary"><i class="bi bi-download"></i> Export CSV</a>
  </div>
</div>

<div class="panel no-print">
  <form class="row g-2" method="GET">
    <div class="col-md-3">
      <select name="exam_id" class="form-select">
        <option value="">All Exams</option>
        <?php foreach ($exams as $e): ?>
          <option value="<?= (int)$e['exam_id'] ?>" <?= $examFilter == $e['exam_id'] ? 'selected' : '' ?>>
            <?= h($e['subject_code'] . ' - ' . $e['subject_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <select name="department" class="form-select">
        <option value="">All Departments</option>
        <?php foreach ($departments as $d): ?>
          <option value="<?= h($d) ?>" <?= $deptFilter === $d ? 'selected' : '' ?>><?= h($d) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <select name="hall_id" class="form-select">
        <option value="">All Halls</option>
        <?php foreach ($halls as $h): ?>
          <option value="<?= (int)$h['hall_id'] ?>" <?= $hallFilter == $h['hall_id'] ? 'selected' : '' ?>><?= h($h['hall_number']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3">
      <input type="text" name="search" class="form-control" placeholder="Search Register No / Hall No" value="<?= h($regSearch) ?>">
    </div>
    <div class="col-md-2">
      <button class="btn btn-outline-primary w-100" type="submit"><i class="bi bi-search"></i> Apply</button>
    </div>
  </form>
</div>

<div class="panel">
  <p class="text-muted">Showing <?= count($rows) ?> allocation record(s).</p>
  <div class="table-responsive">
    <table class="table align-middle" id="reportTable">
      <thead>
        <tr>
          <th>Register No</th>
          <th>Student Name</th>
          <th>Department</th>
          <th>Exam</th>
          <th>Hall No</th>
          <th>Seat No</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$rows): ?>
          <tr><td colspan="6" class="text-muted text-center py-4">No allocation records found. Generate an allocation first from <a href="allocate.php">here</a>.</td></tr>
        <?php endif; ?>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= h($r['register_no']) ?></td>
            <td><?= h($r['name']) ?></td>
            <td><span class="badge badge-dept"><?= h($r['department']) ?></span></td>
            <td><?= h($r['subject_code'] . ' - ' . $r['subject_name']) ?><br><small class="text-muted"><?= h(date('d M Y', strtotime($r['exam_date']))) ?></small></td>
            <td><?= h($r['hall_number']) ?></td>
            <td><strong><?= h($r['seat_no']) ?></strong></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
