<?php
/**
 * Database connection using PDO.
 * Update these settings if your MySQL username/password is different.
 */

$DB_HOST = 'localhost';
$DB_NAME = 'exam_hall_allocation';
$DB_USER = 'root';
$DB_PASS = ''; // default XAMPP password is empty

try {
    $pdo = new PDO(
        "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die('Database connection failed: ' . htmlspecialchars($e->getMessage()));
}
