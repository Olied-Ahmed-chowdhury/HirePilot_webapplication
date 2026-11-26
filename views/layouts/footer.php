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

<?php if (\HirePilot\Core\Auth::check()): ?>
<button type="button" class="hp-chatbot-toggle" id="hp-chatbot-toggle" aria-label="Open HirePilot Guide">
  <i class="bi bi-chat-dots-fill"></i>
  <span>HirePilot Guide</span>
</button>

<div class="hp-chatbot-panel" id="hp-chatbot-panel" role="dialog" aria-label="HirePilot AI Assistant">
  <div class="hp-chatbot-header">
    <div>
      <div class="fw-bold small">HirePilot Website Guide</div>
      <div style="font-size: 0.72rem; color: #cbd5e1;">Ask anything about using HirePilot AI</div>
    </div>
    <button type="button" class="btn-close btn-close-white btn-sm" id="hp-chatbot-close" aria-label="Close"></button>
  </div>
  <div class="hp-chatbot-messages" id="hp-chatbot-messages">
    <div class="hp-chatbot-bubble bot">Hi! I am your HirePilot Guide. Ask me how to upload your resume, sync live LinkedIn/ATS jobs, draft AI emails, or track applications.</div>
  </div>
  <div class="hp-chatbot-chips">
    <button type="button" class="hp-chatbot-chip" onclick="askGuide('How do I find LinkedIn jobs matching my resume?')">Match my resume</button>
    <button type="button" class="hp-chatbot-chip" onclick="askGuide('How do I sync latest live jobs?')">Sync live jobs</button>
    <button type="button" class="hp-chatbot-chip" onclick="askGuide('How do I draft an AI cover letter?')">Draft AI email</button>
    <button type="button" class="hp-chatbot-chip" onclick="askGuide('How do I track my applications?')">Track applications</button>
  </div>
  <form class="hp-chatbot-form" id="hp-chatbot-form">
    <input type="text" class="form-control form-control-sm" id="hp-chatbot-input" placeholder="Ask a question..." autocomplete="off" required>
    <button type="submit" class="btn btn-primary btn-sm" id="hp-chatbot-send"><i class="bi bi-send-fill"></i></button>
  </form>
</div>

<script>
(function () {
  const toggleBtn = document.getElementById('hp-chatbot-toggle');
  const closeBtn  = document.getElementById('hp-chatbot-close');
  const panel     = document.getElementById('hp-chatbot-panel');
  const form      = document.getElementById('hp-chatbot-form');
  const input     = document.getElementById('hp-chatbot-input');
  const messages  = document.getElementById('hp-chatbot-messages');
  const history   = [];

  if (!toggleBtn || !panel) return;

  toggleBtn.addEventListener('click', () => {
    panel.classList.toggle('open');
    if (panel.classList.contains('open')) input.focus();
  });
  closeBtn.addEventListener('click', () => panel.classList.remove('open'));

  function appendBubble(text, role) {
    const div = document.createElement('div');
    div.className = 'hp-chatbot-bubble ' + role;
    div.textContent = text;
    messages.appendChild(div);
    messages.scrollTop = messages.scrollHeight;
    return div;
  }

  window.askGuide = async function (question) {
    if (!question || !question.trim()) return;
    panel.classList.add('open');
    appendBubble(question, 'user');
    const loading = appendBubble('Thinking...', 'bot');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

    try {
      const res = await fetch(window.BASE_URL + '/assistant/chat', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify({ message: question, history: history, _csrf_token: csrf })
      });
      const data = await res.json();
      const reply = data.reply || data.error || 'Sorry, I could not answer right now.';
      loading.textContent = reply;
      history.push({ role: 'user', content: question }, { role: 'assistant', content: reply });
    } catch (e) {
      loading.textContent = 'Connection error. Please try again.';
    }
  };

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    const q = input.value.trim();
    if (!q) return;
    input.value = '';
    window.askGuide(q);
  });
})();
</script>
<?php endif; ?>

<?= $extraScripts ?? '' ?>
</body>
</html>
