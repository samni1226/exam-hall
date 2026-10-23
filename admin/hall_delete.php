<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    $stmt = $pdo->prepare('DELETE FROM halls WHERE hall_id = ?');
    $stmt->execute([$id]);
    set_flash('success', 'Hall deleted successfully.');
}
redirect('halls.php');
