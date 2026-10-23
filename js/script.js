// Auto-hide flash alerts after 4 seconds
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.alert-auto-hide').forEach(function (el) {
    setTimeout(function () {
      el.style.transition = 'opacity .5s ease';
      el.style.opacity = '0';
      setTimeout(function () { el.remove(); }, 500);
    }, 4000);
  });

  // Confirm before any delete action
  document.querySelectorAll('.confirm-delete').forEach(function (link) {
    link.addEventListener('click', function (e) {
      if (!confirm('Are you sure you want to delete this record? This cannot be undone.')) {
        e.preventDefault();
      }
    });
  });

  // Generic client-side "required fields" check for forms with class needs-validation
  document.querySelectorAll('form.needs-validation').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      let valid = true;
      form.querySelectorAll('[required]').forEach(function (field) {
        if (!field.value || !field.value.trim()) {
          valid = false;
          field.classList.add('is-invalid');
        } else {
          field.classList.remove('is-invalid');
        }
      });
      if (!valid) {
        e.preventDefault();
        alert('Please fill in all required fields.');
      }
    });
  });
});

// Live filter for report tables (search by register no / hall no client side, in addition to server-side search)
function liveFilterTable(inputId, tableId, columnIndex) {
  const input = document.getElementById(inputId);
  if (!input) return;
  input.addEventListener('keyup', function () {
    const filter = input.value.toLowerCase();
    const rows = document.querySelectorAll('#' + tableId + ' tbody tr');
    rows.forEach(function (row) {
      const cell = row.cells[columnIndex];
      if (!cell) return;
      row.style.display = cell.textContent.toLowerCase().includes(filter) ? '' : 'none';
    });
  });
}
