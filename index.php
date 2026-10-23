<?php
require_once __DIR__ . '/includes/functions.php';

if (!empty($_SESSION['admin_id'])) {
    redirect('admin/dashboard.php');
}
if (!empty($_SESSION['student_id'])) {
    redirect('student/dashboard.php');
}
redirect('login.php');
