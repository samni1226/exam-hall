<?php
require_once __DIR__ . '/includes/functions.php';
unset($_SESSION['student_id'], $_SESSION['student_register_no'], $_SESSION['student_name']);
session_destroy();
header('Location: student_login.php');
exit;
