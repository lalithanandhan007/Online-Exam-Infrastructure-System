/**
 * main.js – Navbar toggle, copy-to-clipboard, misc UI helpers
 */

(function () {
  'use strict';

  /* ── Mobile Navbar Toggle ───────────────────────────────── */
  const hamburger = document.getElementById('hamburger');
  const navMenu   = document.getElementById('nav-menu');
  if (hamburger && navMenu) {
    hamburger.addEventListener('click', () => {
      navMenu.classList.toggle('open');
    });
  }

  /* ── Dashboard Sidebar Toggle ───────────────────────────── */
  const sidebarToggle = document.getElementById('sidebar-toggle');
  const sidebar       = document.getElementById('sidebar');
  const overlay       = document.getElementById('sidebar-overlay');

  function closeSidebar() {
    sidebar?.classList.remove('open');
    overlay?.classList.remove('open');
  }

  sidebarToggle?.addEventListener('click', () => {
    sidebar?.classList.toggle('open');
    overlay?.classList.toggle('open');
  });
  overlay?.addEventListener('click', closeSidebar);

  /* ── Copy Room Code ─────────────────────────────────────── */
  document.querySelectorAll('.copy-code').forEach((btn) => {
    btn.addEventListener('click', () => {
      const code = btn.dataset.code;
      if (!code) return;
      navigator.clipboard.writeText(code).then(() => {
        const original = btn.innerHTML;
        btn.innerHTML = '✅ Copied!';
        setTimeout(() => { btn.innerHTML = original; }, 2000);
      }).catch(() => {
        // Fallback
        const ta = document.createElement('textarea');
        ta.value = code;
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
      });
    });
  });

  /* ── Auto-dismiss alerts ────────────────────────────────── */
  document.querySelectorAll('.alert[data-auto-dismiss]').forEach((alert) => {
    const delay = parseInt(alert.dataset.autoDismiss, 10) || 4000;
    setTimeout(() => {
      alert.style.transition = 'opacity 0.5s ease';
      alert.style.opacity = '0';
      setTimeout(() => alert.remove(), 500);
    }, delay);
  });

  /* ── Password Visibility Toggle ─────────────────────────── */
  document.querySelectorAll('.toggle-password').forEach((btn) => {
    btn.addEventListener('click', () => {
      const target = document.getElementById(btn.dataset.target);
      if (!target) return;
      if (target.type === 'password') {
        target.type = 'text';
        btn.textContent = '🙈';
      } else {
        target.type = 'password';
        btn.textContent = '👁️';
      }
    });
  });

  /* ── Confirm Delete ─────────────────────────────────────── */
  document.querySelectorAll('[data-confirm]').forEach((el) => {
    el.addEventListener('click', (e) => {
      if (!confirm(el.dataset.confirm)) e.preventDefault();
    });
  });

  /* ── Question Type Switcher (create_exam manage_questions) ─ */
  document.querySelectorAll('.q-type-select').forEach((sel) => {
    sel.addEventListener('change', () => {
      const parent    = sel.closest('.question-builder-block');
      const mcqBlock  = parent?.querySelector('.mcq-options-block');
      if (!mcqBlock) return;
      mcqBlock.style.display = sel.value === 'mcq' ? 'block' : 'none';
    });
    // init
    const parent   = sel.closest('.question-builder-block');
    const mcqBlock = parent?.querySelector('.mcq-options-block');
    if (mcqBlock) {
      mcqBlock.style.display = sel.value === 'mcq' ? 'block' : 'none';
    }
  });

})();
