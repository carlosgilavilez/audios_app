import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Theme: persist + toggle (global)
(() => {
  const storageKey = 'theme';
  const root = document.documentElement;

  const apply = (mode) => {
    if (mode === 'dark') {
      root.classList.add('dark');
    } else {
      root.classList.remove('dark');
    }
  };

  const getInitial = () => {
    const saved = localStorage.getItem(storageKey);
    if (saved === 'light' || saved === 'dark') return saved;
    // Default to light if nothing stored (explicit request for less contrasty light)
    return 'light';
  };

  const setTheme = (mode) => {
    localStorage.setItem(storageKey, mode);
    apply(mode);
    document.querySelectorAll('[data-theme-toggle] [data-icon]')
      .forEach((el) => {
        const on = el.getAttribute('data-icon');
        el.classList.toggle('hidden', (mode === 'dark' && on === 'sun') || (mode === 'light' && on === 'moon'));
      });
  };

  // Init
  const initial = getInitial();
  apply(initial);
  // Initialize icons visibility on DOM ready
  document.addEventListener('DOMContentLoaded', () => setTheme(getInitial()));

  // Bind all toggles
  document.addEventListener('click', (e) => {
    const btn = (e.target instanceof Element) ? e.target.closest('[data-theme-toggle]') : null;
    if (!btn) return;
    e.preventDefault();
    const next = root.classList.contains('dark') ? 'light' : 'dark';
    setTheme(next);
  });
})();

// --- Admin/editor presence indicator ---
try {
    const roleMeta = document.querySelector('meta[name="user-role"]');
    const idMeta = document.querySelector('meta[name="user-id"]');
    const userRole = roleMeta ? roleMeta.getAttribute('content') : null;
    const myId = idMeta ? parseInt(idMeta.getAttribute('content')) : null;
    const shouldJoin = (userRole === 'admin' || userRole === 'editor');
    const shouldRenderBadge = (userRole === 'admin');
    if (shouldJoin && window.Echo && document.body) {
        let badge = null, dot = null, label = null;
        if (shouldRenderBadge) {
            const header = document.querySelector('header');
            const badgeId = 'presence-badge';
            if (header && !document.getElementById(badgeId)) {
                badge = document.createElement('div');
                badge.id = badgeId;
                badge.style.display = 'flex';
                badge.style.alignItems = 'center';
                badge.style.gap = '6px';
                badge.title = 'Usuarios en línea (admin/editor)';
                badge.innerHTML = '<span style="width:8px;height:8px;border-radius:50%;background:#9CA3AF;display:inline-block"></span><span style="font-size:12px;color:#6B7280">En línea: 0</span>';
                const actions = header.querySelector('.flex.items-center.gap-2, .flex.items-center.gap-2.shrink-0') || header.lastElementChild;
                if (actions) actions.prepend(badge);
                dot = badge.firstChild;
                label = badge.lastChild;
            }
        }

        let members = [];
        const updateUI = () => {
            if (!shouldRenderBadge || !badge || !dot || !label) return;
            const others = members.filter(u => u.id !== myId);
            // Compose visible names (limit to 3 then +N más)
            const names = others.map(u => `${u.name} (${u.role})`);
            const visible = names.slice(0, 3).join(', ');
            const more = names.length > 3 ? ` +${names.length - 3} más` : '';
            const suffix = names.length ? ` — ${visible}${more}` : '';
            label.textContent = `En línea: ${others.length}${suffix}`;
            dot.style.background = others.length > 0 ? '#22C55E' : '#9CA3AF';
            badge.title = names.join(', ');
        };

        window.Echo.join('presence.control-panel')
            .here((users) => { members = users; updateUI(); })
            .joining((user) => { members.push(user); updateUI(); })
            .leaving((user) => { members = members.filter(u => u.id !== user.id); updateUI(); });
    }
} catch (e) {
    console.warn('Presence init failed', e);
}

// --- Editing lock client helper ---
window.ContentLock = (function () {
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrf = csrfMeta ? csrfMeta.getAttribute('content') : '';
    let beat = null;

    function disableForm(form) {
        form.querySelectorAll('input, select, textarea, button').forEach(el => {
            if (el.type !== 'button') el.setAttribute('disabled', 'disabled');
        });
    }

    function banner(container, text) {
        if (!container) return;
        container.innerHTML = `<div style="background:#FEF2F2;color:#991B1B;border:1px solid #FCA5A5;padding:10px;border-radius:8px;margin-bottom:12px">${text}</div>`;
    }

    async function acquire(type, id, form, noticeEl) {
        try {
            const res = await fetch('/locks/acquire', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ type, id })
            });
            if (res.status === 423) {
                const data = await res.json();
                banner(noticeEl, `Este contenido está siendo editado por ${data.by}.`);
                disableForm(form);
                return false;
            }
            // Start heartbeat
            beat = setInterval(() => heartbeat(type, id), 30000);
            window.addEventListener('beforeunload', () => release(type, id), { once: true });
            document.addEventListener('visibilitychange', () => {
                if (document.visibilityState === 'hidden') release(type, id);
            });
            return true;
        } catch (e) {
            console.warn('Lock acquire failed', e);
            return false;
        }
    }

    async function heartbeat(type, id) {
        try {
            await fetch('/locks/heartbeat', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ type, id })
            });
        } catch {}
    }

    async function release(type, id) {
        try {
            if (beat) clearInterval(beat);
            await fetch('/locks/release', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ type, id })
            });
        } catch {}
    }

    return { acquire, release };
})();
