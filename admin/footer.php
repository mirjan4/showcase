  </div><!-- /.admin-content -->
</div><!-- /.admin-main -->

<!-- Reusable Delete Confirmation Modal -->
<div class="modal-overlay" id="deleteModal" role="dialog" aria-modal="true" aria-label="Confirm Deletion">
  <div class="modal-card">
    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
      <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(239, 68, 68, 0.15); color: #ef4444; display: grid; place-items: center; flex-shrink: 0;">
        <?= lucide('trash-2', '', 20) ?>
      </div>
      <div>
        <h3 style="font-size: 1.15rem; font-weight: 700; margin: 0;">Confirm Permanent Deletion</h3>
        <p style="font-size: 0.813rem; color: var(--admin-muted); margin: 2px 0 0;">This action cannot be undone.</p>
      </div>
    </div>

    <p style="font-size: 0.938rem; color: var(--admin-text); margin-bottom: 24px;">
      Are you sure you want to delete <strong id="deleteModalItemTitle" style="color: #f87171;">this item</strong>? All associated files, gallery screenshots, and records will be removed.
    </p>

    <form id="deleteModalForm" method="POST" action="">
      <?= csrf_field() ?>
      <div style="display: flex; justify-content: flex-end; gap: 12px;">
        <button class="btn btn-secondary" id="deleteModalCancel" type="button" style="padding: 9px 18px; font-size: 0.875rem;">
          Cancel
        </button>
        <button class="btn btn-primary" type="submit" style="background: var(--admin-danger); border-color: var(--admin-danger); padding: 9px 18px; font-size: 0.875rem;">
          Delete Permanently
        </button>
      </div>
    </form>
  </div>
</div>

<script src="<?= ASSETS_URL ?>/js/admin.js"></script>
</body>
</html>
