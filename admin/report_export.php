<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';
require_admin();

$examFilter = trim($_GET['exam_id'] ?? '');
$deptFilter = trim($_GET['department'] ?? '');
$hallFilter = trim($_GET['hall_id'] ?? '');
$regSearch  = trim($_GET['search'] ?? '');

$sql = "
  SELECT s.register_no, s.name, s.department, e.subject_code, e.subject_name, h.hall_number, a.seat_no
  FROM allocations a
  JOIN students s ON s.student_id = a.student_id
  JOIN exams e    ON e.exam_id = a.exam_id
  JOIN halls h    ON h.hall_id = a.hall_id
  WHERE 1=1
";
$params = [];
if ($examFilter !== '') { $sql .= ' AND a.exam_id = ?'; $params[] = $examFilter; }
if ($deptFilter !== '') { $sql .= ' AND s.department = ?'; $params[] = $deptFilter; }
if ($hallFilter !== '') { $sql .= ' AND a.hall_id = ?'; $params[] = $hallFilter; }
if ($regSearch !== '') {
    $sql .= ' AND (s.register_no LIKE ? OR h.hall_number LIKE ?)';
    $params[] = "%$regSearch%";
    $params[] = "%$regSearch%";
}
$sql .= ' ORDER BY h.hall_number ASC, a.seat_no ASC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=allocation_report_' . date('Y-m-d_His') . '.csv');

$out = fopen('php://output', 'w');
fputcsv($out, ['Register No', 'Student Name', 'Department', 'Subject Code', 'Subject Name', 'Hall No', 'Seat No']);
foreach ($rows as $r) {
    fputcsv($out, [
        $r['register_no'], $r['name'], $r['department'],
        $r['subject_code'], $r['subject_name'], $r['hall_number'], $r['seat_no'],
    ]);
}
fclose($out);
exit;
