/**
 * exam.js – Timer, anti-cheat, progress, and auto-submit logic
 * for the Online Examination Infrastructure System
 */

(function () {
  'use strict';

  /* ── Exam Timer ─────────────────────────────────────────── */
  const timerEl    = document.getElementById('timer-display');
  const timerWrap  = document.getElementById('exam-timer-bar');
  const examForm   = document.getElementById('exam-form');
  const durationEl = document.getElementById('exam-duration'); // hidden input (minutes)

  if (!timerEl || !durationEl || !examForm) return;

  let totalSeconds = parseInt(durationEl.value, 10) * 60;
  let timerInterval;
  let warningShown  = false;
  let autoSubmitting = false;

  function formatTime(secs) {
    const h = Math.floor(secs / 3600);
    const m = Math.floor((secs % 3600) / 60);
    const s = secs % 60;
    if (h > 0) {
      return `${pad(h)}:${pad(m)}:${pad(s)}`;
    }
    return `${pad(m)}:${pad(s)}`;
  }

  function pad(n) { return String(n).padStart(2, '0'); }

  function tick() {
    totalSeconds--;
    timerEl.textContent = formatTime(totalSeconds);

    // Warning at 2 minutes
    if (totalSeconds <= 120 && !warningShown) {
      warningShown = true;
      timerWrap.classList.add('timer-warning');
      showNotification('⚠️ Only 2 minutes remaining!', 'warning');
    }

    // Auto-submit when time runs out
    if (totalSeconds <= 0) {
      clearInterval(timerInterval);
      if (!autoSubmitting) {
        autoSubmitting = true;
        showNotification('⏰ Time is up! Submitting automatically…', 'danger');
        setTimeout(() => {
          document.getElementById('auto-submit-flag').value = '1';
          examForm.submit();
        }, 1500);
      }
    }
  }

  // Restore from sessionStorage if page was refreshed
  const storageKey = 'exam_timer_' + (document.getElementById('attempt-id')?.value || 'x');
  const savedTime  = sessionStorage.getItem(storageKey);
  if (savedTime !== null) {
    totalSeconds = Math.min(parseInt(savedTime, 10), totalSeconds);
  }

  timerEl.textContent = formatTime(totalSeconds);
  timerInterval = setInterval(tick, 1000);

  // Persist every second
  setInterval(() => {
    sessionStorage.setItem(storageKey, totalSeconds);
  }, 1000);

  /* ── Anti-Cheat: Visibility Change ─────────────────────── */
  let tabSwitches = 0;
  const maxSwitches = 3;
  const switchCounter = document.getElementById('tab-switch-count');

  document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
      tabSwitches++;
      if (switchCounter) switchCounter.value = tabSwitches;
      if (tabSwitches >= maxSwitches) {
        showNotification('🚫 Too many tab switches! Exam is being submitted.', 'danger');
        setTimeout(() => {
          document.getElementById('auto-submit-flag').value = '1';
          examForm.submit();
        }, 2000);
      } else {
        showNotification(
          `⚠️ Tab switch detected (${tabSwitches}/${maxSwitches}). Exam may auto-submit.`,
          'warning'
        );
      }
    }
  });

  /* ── Anti-Cheat: Disable Right-Click & F12 ──────────────── */
  document.addEventListener('contextmenu', (e) => e.preventDefault());
  document.addEventListener('keydown', (e) => {
    if (e.key === 'F12' || (e.ctrlKey && e.shiftKey && e.key === 'I') ||
        (e.ctrlKey && e.key === 'u')) {
      e.preventDefault();
    }
  });

  /* ── Question Progress Tracker ──────────────────────────── */
  const answeredCounts = document.getElementById('answered-count');
  const totalCount     = document.getElementById('total-count');
  const progressFill   = document.getElementById('progress-fill');

  function updateProgress() {
    if (!answeredCounts || !totalCount || !progressFill) return;
    const total    = parseInt(totalCount.textContent, 10) || 0;
    const answered = countAnswered();
    answeredCounts.textContent = answered;
    const pct = total > 0 ? Math.round((answered / total) * 100) : 0;
    progressFill.style.width = pct + '%';
  }

  function countAnswered() {
    let count = 0;
    // MCQs
    document.querySelectorAll('.question-mcq').forEach((qDiv) => {
      if (qDiv.querySelector('input[type="radio"]:checked')) count++;
    });
    // Descriptive
    document.querySelectorAll('.question-desc').forEach((qDiv) => {
      const ta = qDiv.querySelector('textarea');
      if (ta && ta.value.trim().length > 0) count++;
    });
    return count;
  }

  document.addEventListener('change', updateProgress);
  document.addEventListener('input', updateProgress);
  updateProgress();

  /* ── Form Submit Guard ──────────────────────────────────── */
  examForm.addEventListener('submit', (e) => {
    const answered = countAnswered();
    const total    = parseInt(totalCount?.textContent, 10) || 0;
    const flag     = document.getElementById('auto-submit-flag')?.value;

    if (flag !== '1' && answered < total) {
      const unanswered = total - answered;
      const ok = confirm(
        `You have ${unanswered} unanswered question(s).\nDo you still want to submit?`
      );
      if (!ok) { e.preventDefault(); return; }
    }
    clearInterval(timerInterval);
    sessionStorage.removeItem(storageKey);
  });

  /* ── Notification Toast ─────────────────────────────────── */
  function showNotification(msg, type = 'info') {
    const old = document.getElementById('exam-toast');
    if (old) old.remove();

    const colors = {
      warning: '#f59e0b',
      danger:  '#ef4444',
      info:    '#2563eb',
      success: '#10b981',
    };
    const toast = document.createElement('div');
    toast.id = 'exam-toast';
    Object.assign(toast.style, {
      position: 'fixed', bottom: '24px', right: '24px', zIndex: '9999',
      background: colors[type] || colors.info,
      color: '#fff', padding: '14px 22px', borderRadius: '12px',
      fontWeight: '600', fontSize: '0.95rem',
      boxShadow: '0 8px 30px rgba(0,0,0,0.25)',
      maxWidth: '340px', opacity: '0', transition: 'opacity 0.3s ease',
    });
    toast.textContent = msg;
    document.body.appendChild(toast);
    requestAnimationFrame(() => { toast.style.opacity = '1'; });
    setTimeout(() => {
      toast.style.opacity = '0';
      setTimeout(() => toast.remove(), 300);
    }, 4000);
  }

})();
