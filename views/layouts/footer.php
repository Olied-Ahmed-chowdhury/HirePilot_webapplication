<?php
/**
 * HirePilot AI — Layout: Footer + Scripts
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 */
declare(strict_types=1);
?>
  </div><!-- /.app-content -->
  </main><!-- /.app-main -->
</div><!-- /.app-wrapper -->

<!-- Shared Confirm Modal -->
<div class="modal fade" id="confirm-modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-body text-center p-4">
        <div class="fs-2 mb-2">⚠️</div>
        <div class="fw-700 mb-1" style="font-size:1rem">Are you sure?</div>
        <div class="text-muted confirm-msg mb-3" style="font-size:.875rem"></div>
        <div class="d-flex gap-2 justify-content-center">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-danger btn-sm btn-confirm">Confirm</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Drawer Overlay -->
<div class="drawer-overlay" id="drawer-overlay"></div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- HirePilot App JS -->
<script src="<?= BASE_URL ?>/assets/js/app.js"></script>

<!-- Page-specific scripts -->
<?= $extraScripts ?? '' ?>

<!-- Auto-resize textareas -->
<script>
document.querySelectorAll('textarea[data-autoresize]').forEach(ta => {
  ta.addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = this.scrollHeight + 'px';
  });
});

// Global search redirect
const gs = document.getElementById('global-search');
if (gs) {
  gs.addEventListener('keydown', debounce(e => {
    if (e.key === 'Enter' && gs.value.trim()) {
      window.location.href = `<?= BASE_URL ?>/jobs?q=${encodeURIComponent(gs.value.trim())}`;
    }
  }, 300));
}

// Mark all notifications read
const marAll = document.getElementById('mark-all-read');
if (marAll) {
  marAll.addEventListener('click', async e => {
    e.preventDefault();
    try {
      await Http.post('<?= BASE_URL ?>/notifications/read-all');
      document.getElementById('notif-badge').style.display = 'none';
      Toast.success('All notifications marked as read');
    } catch {}
  });
}
</script>

</body>
</html>

