<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('student_login.php');
}

$register_no = trim($_POST['register_no'] ?? '');
$password    = $_POST['password'] ?? '';

if ($register_no === '' || $password === '') {
    set_flash('error', 'Please enter both register number and password.');
    redirect('student_login.php');
}

$stmt = $pdo->prepare('SELECT student_id, register_no, name, password FROM students WHERE register_no = ? LIMIT 1');
$stmt->execute([$register_no]);
$student = $stmt->fetch();

if ($student && password_verify($password, $student['password'])) {
    session_regenerate_id(true);
    $_SESSION['student_id']          = $student['student_id'];
    $_SESSION['student_register_no'] = $student['register_no'];
    $_SESSION['student_name']        = $student['name'];
    redirect('student/dashboard.php');
} else {
    set_flash('error', 'Invalid register number or password.');
    redirect('student_login.php');
}
