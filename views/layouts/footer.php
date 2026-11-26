<?php
/**
 * HirePilot AI — Layout: Footer + Scripts
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 */
declare(strict_types=1);
?>
    </div>
  </main>
</div>

<div class="modal fade" id="confirm-modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-body text-center p-4">
        <div class="fs-3 text-warning mb-2"><i class="bi bi-exclamation-triangle"></i></div>
        <div class="fw-semibold mb-1">Confirm Action</div>
        <div class="text-muted confirm-msg mb-3 small"></div>
        <div class="d-flex gap-2 justify-content-center">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-danger btn-sm btn-confirm">Confirm</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>window.BASE_URL = <?= json_encode(BASE_URL) ?>;</script>
<script src="<?= BASE_URL ?>/assets/js/app.js"></script>
<?= $extraScripts ?? '' ?>
</body>
</html>
