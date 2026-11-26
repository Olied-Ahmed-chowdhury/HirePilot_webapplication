/* ============================================================
   HirePilot AI — Application JavaScript
   Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
   ============================================================ */

'use strict';

/* ── 1. Toast Notification System ────────────────────────────── */
const Toast = (() => {
  let container = null;

  function getContainer() {
    if (!container) {
      container = document.createElement('div');
      container.id = 'toast-container';
      document.body.appendChild(container);
    }
    return container;
  }

  const icons = {
    success: '<i class="bi bi-check-circle-fill"></i>',
    error:   '<i class="bi bi-x-circle-fill"></i>',
    warning: '<i class="bi bi-exclamation-triangle-fill"></i>',
    info:    '<i class="bi bi-info-circle-fill"></i>',
  };

  const titles = { success: 'Success', error: 'Error', warning: 'Warning', info: 'Info' };

  function show(message, type = 'info', duration = 4000) {
    const c    = getContainer();
    const el   = document.createElement('div');
    el.className = `hp-toast toast-${type}`;
    el.innerHTML = `
      <div class="toast-icon">${icons[type] || icons.info}</div>
      <div class="toast-body">
        <div class="toast-title">${titles[type] || 'Notice'}</div>
        <div class="toast-msg">${message}</div>
      </div>
      <button class="toast-close" aria-label="Dismiss">&times;</button>`;

    c.appendChild(el);

    el.querySelector('.toast-close').addEventListener('click', () => dismiss(el));

    if (duration > 0) {
      setTimeout(() => dismiss(el), duration);
    }
    return el;
  }

  function dismiss(el) {
    el.style.animation = 'toastOut .3s ease forwards';
    el.addEventListener('animationend', () => el.remove(), { once: true });
  }

  return {
    success: (msg, d) => show(msg, 'success', d),
    error:   (msg, d) => show(msg, 'error',   d),
    warning: (msg, d) => show(msg, 'warning', d),
    info:    (msg, d) => show(msg, 'info',    d),
  };
})();

window.Toast = Toast;

/* ── 2. AJAX / Fetch Wrapper ──────────────────────────────────── */
const Http = (() => {
  function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
  }

  async function request(url, options = {}) {
    const defaults = {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-Token': getCsrfToken(),
        ...options.headers,
      },
    };

    if (options.body && typeof options.body === 'object' && !(options.body instanceof FormData)) {
      defaults.headers['Content-Type'] = 'application/json';
      options.body = JSON.stringify(options.body);
    }

    const response = await fetch(url, { ...options, ...defaults, headers: { ...defaults.headers, ...(options.headers || {}) } });

    const contentType = response.headers.get('content-type') || '';
    let data;
    if (contentType.includes('application/json')) {
      data = await response.json();
    } else {
      data = await response.text();
    }

    if (!response.ok) {
      const err = new Error((typeof data === 'object' ? data.message : data) || `HTTP ${response.status}`);
      err.status = response.status;
      err.data   = data;
      throw err;
    }

    return data;
  }

  return {
    get:    (url, opts = {}) => request(url, { method: 'GET',    ...opts }),
    post:   (url, body, opts = {}) => request(url, { method: 'POST',   body, ...opts }),
    put:    (url, body, opts = {}) => request(url, { method: 'PUT',    body, ...opts }),
    delete: (url, opts = {}) => request(url, { method: 'DELETE', ...opts }),
    patch:  (url, body, opts = {}) => request(url, { method: 'PATCH',  body, ...opts }),
  };
})();

window.Http = Http;

/* ── 3. Button Loading State ─────────────────────────────────── */
function btnLoading(btn, loading = true) {
  if (loading) {
    btn.dataset.originalText = btn.innerHTML;
    btn.classList.add('btn-loading');
    btn.disabled = true;
  } else {
    btn.innerHTML = btn.dataset.originalText || btn.innerHTML;
    btn.classList.remove('btn-loading');
    btn.disabled = false;
  }
}

window.btnLoading = btnLoading;

/* ── 4. Sidebar Toggle (Mobile) ──────────────────────────────── */
function initSidebar() {
  const sidebar  = document.querySelector('.app-sidebar');
  const overlay  = document.getElementById('sidebar-overlay');
  const toggles  = document.querySelectorAll('.navbar-toggle, [data-sidebar-toggle]');

  if (!sidebar) return;

  function open() {
    sidebar.classList.add('open');
    overlay && overlay.classList.add('show');
    document.body.style.overflow = 'hidden';
  }

  function close() {
    sidebar.classList.remove('open');
    overlay && overlay.classList.remove('show');
    document.body.style.overflow = '';
  }

  toggles.forEach(btn => btn.addEventListener('click', () => {
    sidebar.classList.contains('open') ? close() : open();
  }));

  overlay && overlay.addEventListener('click', close);
}

/* ── 5. Skills Tag Input ─────────────────────────────────────── */
function initSkillsInput(containerId, hiddenInputId) {
  const container   = document.getElementById(containerId);
  const hiddenInput = document.getElementById(hiddenInputId);
  if (!container || !hiddenInput) return;

  let skills = [];

  try {
    const val = hiddenInput.value.trim();
    if (val) skills = JSON.parse(val);
  } catch (e) { skills = []; }

  function render() {
    const tagEls = container.querySelectorAll('.skill-tag');
    tagEls.forEach(t => t.remove());

    const input = container.querySelector('.skill-tag-input');

    skills.forEach((skill, i) => {
      const tag = document.createElement('span');
      tag.className = 'skill-tag';
      tag.innerHTML = `${escHtml(skill)} <button type="button" aria-label="Remove">&times;</button>`;
      tag.querySelector('button').addEventListener('click', () => {
        skills.splice(i, 1);
        render();
      });
      container.insertBefore(tag, input);
    });

    hiddenInput.value = JSON.stringify(skills);
  }

  const input = container.querySelector('.skill-tag-input');
  if (input) {
    input.addEventListener('keydown', e => {
      if ((e.key === 'Enter' || e.key === ',') && input.value.trim()) {
        e.preventDefault();
        const skill = input.value.trim().replace(/,$/, '');
        if (skill && !skills.includes(skill)) {
          skills.push(skill);
          render();
        }
        input.value = '';
      }
      if (e.key === 'Backspace' && !input.value && skills.length) {
        skills.pop();
        render();
      }
    });
    container.addEventListener('click', () => input.focus());
  }

  render();
}

window.initSkillsInput = initSkillsInput;

/* ── 6. Notifications AJAX ───────────────────────────────────── */
function initNotifications() {
  const badge = document.getElementById('notif-badge');
  const list  = document.getElementById('notif-list');
  if (!list) return;

  async function loadNotifications() {
    try {
      const base = window.BASE_URL || '';
      const data = await Http.get(`${base}/notifications/fetch`);
      if (badge) {
        badge.textContent = data.unread || '';
        badge.style.display = data.unread ? 'flex' : 'none';
      }
      if (list && data.notifications) {
        renderNotifications(data.notifications);
      }
    } catch (e) { /* silent */ }
  }

  function renderNotifications(items) {
    if (!items.length) {
      list.innerHTML = '<div class="text-center text-muted py-3" style="font-size:.84rem">No notifications yet</div>';
      return;
    }
    list.innerHTML = items.map(n => `
      <div class="notif-item ${n.is_read ? '' : 'unread'}" data-id="${n.id}">
        <div class="notif-item-icon"><i class="bi bi-bell"></i></div>
        <div class="flex-1">
          <div class="notif-item-title">${escHtml(n.title)}</div>
          <div class="notif-item-msg">${escHtml(n.message)}</div>
          <div class="notif-item-time">${timeAgo(n.created_at)}</div>
        </div>
      </div>`).join('');

    list.querySelectorAll('.notif-item').forEach(el => {
      el.addEventListener('click', async () => {
        const id = el.dataset.id;
        el.classList.remove('unread');
        try { const base = window.BASE_URL || ''; await Http.post(`${base}/notifications/${id}/read`); loadNotifications(); } catch {}
      });
    });
  }

  loadNotifications();
  setInterval(loadNotifications, 30000); // poll every 30s
}

/* ── 7. Status Update (Applications) ────────────────────────── */
async function updateApplicationStatus(appId, status, btn) {
  btnLoading(btn, true);
  try {
    const data = await Http.post(`/applications/${appId}/status`, { status, _csrf_token: document.querySelector('meta[name="csrf-token"]')?.content });
    Toast.success(`Status updated to "${status.replace(/_/g,' ')}"`);
    const pill = document.querySelector(`[data-app-status="${appId}"]`);
    if (pill) {
      pill.className = `status-pill pill-${status}`;
      pill.textContent = status.replace(/_/g, ' ');
    }
  } catch (e) {
    Toast.error(e.message || 'Failed to update status');
  } finally {
    btnLoading(btn, false);
  }
}

window.updateApplicationStatus = updateApplicationStatus;

/* ── 8. Search Debounce ──────────────────────────────────────── */
function debounce(fn, delay = 350) {
  let timer;
  return (...args) => {
    clearTimeout(timer);
    timer = setTimeout(() => fn(...args), delay);
  };
}

window.debounce = debounce;

/* ── 9. Confirm Dialog ───────────────────────────────────────── */
function confirmAction(message, onConfirm) {
  const modal = document.getElementById('confirm-modal');
  if (!modal) {
    if (window.confirm(message)) onConfirm();
    return;
  }
  modal.querySelector('.confirm-msg').textContent = message;
  const bsModal = new bootstrap.Modal(modal);
  bsModal.show();

  const confirmBtn = modal.querySelector('.btn-confirm');
  confirmBtn.onclick = () => {
    bsModal.hide();
    onConfirm();
  };
}

window.confirmAction = confirmAction;

/* ── 10. Utilities ───────────────────────────────────────────── */
function escHtml(str) {
  const div = document.createElement('div');
  div.appendChild(document.createTextNode(str || ''));
  return div.innerHTML;
}

function timeAgo(dateStr) {
  const diff = Math.floor((Date.now() - new Date(dateStr)) / 1000);
  if (diff < 60)   return 'just now';
  if (diff < 3600) return `${Math.floor(diff/60)}m ago`;
  if (diff < 86400) return `${Math.floor(diff/3600)}h ago`;
  return `${Math.floor(diff/86400)}d ago`;
}

function formatBytes(bytes) {
  if (bytes < 1024) return bytes + ' B';
  if (bytes < 1048576) return (bytes/1024).toFixed(1) + ' KB';
  return (bytes/1048576).toFixed(1) + ' MB';
}

window.escHtml   = escHtml;
window.timeAgo   = timeAgo;
window.formatBytes = formatBytes;

/* ── 11. AI Draft Drawer ─────────────────────────────────────── */
function openDrawer(drawerId) {
  const drawer  = document.getElementById(drawerId);
  const overlay = document.getElementById('drawer-overlay');
  if (drawer)  drawer.classList.add('open');
  if (overlay) overlay.classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closeDrawer(drawerId) {
  const drawer  = document.getElementById(drawerId);
  const overlay = document.getElementById('drawer-overlay');
  if (drawer)  drawer.classList.remove('open');
  if (overlay) overlay.classList.remove('open');
  document.body.style.overflow = '';
}

window.openDrawer  = openDrawer;
window.closeDrawer = closeDrawer;

/* ── 12. File Upload Preview ─────────────────────────────────── */
function initFileUpload(inputId, previewId) {
  const input   = document.getElementById(inputId);
  const preview = document.getElementById(previewId);
  if (!input || !preview) return;

  input.addEventListener('change', () => {
    const file = input.files[0];
    if (!file) { preview.innerHTML = ''; return; }
    preview.innerHTML = `
      <div class="d-flex align-items-center gap-2 mt-2 p-2 rounded-3" style="background:var(--hp-surface-2)">
        <i class="bi bi-file-earmark-text text-primary fs-5"></i>
        <div>
          <div class="fw-600" style="font-size:.84rem">${escHtml(file.name)}</div>
          <div class="text-muted" style="font-size:.75rem">${formatBytes(file.size)}</div>
        </div>
      </div>`;
  });
}

window.initFileUpload = initFileUpload;

/* ── 13. Auto-dismiss Flash Alerts ──────────────────────────── */
function initFlashAlerts() {
  document.querySelectorAll('.alert.fade.show').forEach(alert => {
    setTimeout(() => {
      const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
      if (bsAlert) bsAlert.close();
    }, 5000);
  });
}

/* ── 14. Init on DOM Ready ───────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
  initSidebar();
  initNotifications();
  initFlashAlerts();

  // Drawer overlay close
  const drawerOverlay = document.getElementById('drawer-overlay');
  if (drawerOverlay) {
    drawerOverlay.addEventListener('click', () => {
      document.querySelectorAll('.hp-drawer.open').forEach(d => closeDrawer(d.id));
    });
  }

  // Active sidebar link highlighting
  const currentPath = window.location.pathname;
  document.querySelectorAll('.sidebar-link').forEach(link => {
    const href = link.getAttribute('href');
    if (href && currentPath.startsWith(href) && href !== '/') {
      link.classList.add('active');
    }
  });
});

