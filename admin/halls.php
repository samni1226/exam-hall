<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hall_id     = $_POST['hall_id'] ?? '';
    $hall_number = trim($_POST['hall_number'] ?? '');
    $building    = trim($_POST['building'] ?? '');
    $floor       = trim($_POST['floor'] ?? '');
    $capacity    = (int)($_POST['capacity'] ?? 0);

    $errors = validate_required([
        'Hall Number' => $hall_number,
        'Building'    => $building,
        'Floor'       => $floor,
    ]);
    if ($capacity <= 0) {
        $errors[] = 'Capacity must be a positive number.';
    }

    if ($errors) {
        set_flash('error', implode(' ', $errors));
        redirect('halls.php');
    }

    try {
        if ($hall_id) {
            $stmt = $pdo->prepare('UPDATE halls SET hall_number=?, building=?, floor=?, capacity=? WHERE hall_id=?');
            $stmt->execute([$hall_number, $building, $floor, $capacity, $hall_id]);
            set_flash('success', 'Hall updated successfully.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO halls (hall_number, building, floor, capacity) VALUES (?,?,?,?)');
            $stmt->execute([$hall_number, $building, $floor, $capacity]);
            set_flash('success', 'Hall added successfully.');
        }
    } catch (PDOException $e) {
        set_flash('error', 'Could not save hall. Hall number might already exist.');
    }
    redirect('halls.php');
}

$halls = $pdo->query('SELECT * FROM halls ORDER BY hall_id DESC')->fetchAll();
$totalCapacity = $pdo->query('SELECT COALESCE(SUM(capacity),0) FROM halls')->fetchColumn();

$pageTitle = 'Manage Halls';
$active = 'halls';
require __DIR__ . '/../includes/admin_header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="page-title mb-0">Manage Examination Halls</h3>
  <button class="btn btn-primary no-print" data-bs-toggle="modal" data-bs-target="#hallModal" onclick="openAddModal()">
    <i class="bi bi-plus-lg"></i> Add Hall
  </button>
</div>

<div class="panel">
  <p class="mb-3">Total capacity across all halls: <strong><?= (int)$totalCapacity ?></strong> seats</p>
  <div class="table-responsive">
    <table class="table align-middle">
      <thead>
        <tr><th>Hall Number</th><th>Building</th><th>Floor</th><th>Capacity</th><th class="no-print">Actions</th></tr>
      </thead>
      <tbody>
        <?php if (!$halls): ?>
          <tr><td colspan="5" class="text-muted text-center py-3">No halls added yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($halls as $hall): ?>
          <tr>
            <td><?= h($hall['hall_number']) ?></td>
            <td><?= h($hall['building']) ?></td>
            <td><?= h($hall['floor']) ?></td>
            <td><?= (int)$hall['capacity'] ?></td>
            <td class="no-print">
              <button class="btn btn-sm btn-outline-primary" onclick='openEditModal(<?= json_encode($hall) ?>)'>
                <i class="bi bi-pencil"></i>
              </button>
              <a href="hall_delete.php?id=<?= (int)$hall['hall_id'] ?>" class="btn btn-sm btn-outline-danger confirm-delete">
                <i class="bi bi-trash"></i>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="hallModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" class="needs-validation">
        <div class="modal-header">
          <h5 class="modal-title" id="hallModalTitle">Add Hall</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="hall_id" id="hall_id">
          <div class="mb-2">
            <label class="form-label">Hall Number</label>
            <input type="text" name="hall_number" id="hall_number" class="form-control" required>
          </div>
          <div class="mb-2">
            <label class="form-label">Building</label>
            <input type="text" name="building" id="building" class="form-control" required>
          </div>
          <div class="row">
            <div class="col-md-6 mb-2">
              <label class="form-label">Floor</label>
              <input type="text" name="floor" id="floor" class="form-control" required>
            </div>
            <div class="col-md-6 mb-2">
              <label class="form-label">Capacity</label>
              <input type="number" min="1" name="capacity" id="capacity" class="form-control" required>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Hall</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function openAddModal() {
  document.getElementById('hallModalTitle').innerText = 'Add Hall';
  document.getElementById('hall_id').value = '';
  document.getElementById('hall_number').value = '';
  document.getElementById('building').value = '';
  document.getElementById('floor').value = '';
  document.getElementById('capacity').value = '';
}
function openEditModal(hall) {
  document.getElementById('hallModalTitle').innerText = 'Edit Hall';
  document.getElementById('hall_id').value = hall.hall_id;
  document.getElementById('hall_number').value = hall.hall_number;
  document.getElementById('building').value = hall.building;
  document.getElementById('floor').value = hall.floor;
  document.getElementById('capacity').value = hall.capacity;
  new bootstrap.Modal(document.getElementById('hallModal')).show();
}
</script>

<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
