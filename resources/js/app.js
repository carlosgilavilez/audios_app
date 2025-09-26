import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

const ThemeManager = (() => {
  const defaultTheme = 'spotify';
  const themeKey = 'audios-color-theme';
  const darkKey = 'audios-dark-mode';
  const root = document.documentElement;

  const emit = () => {
    window.dispatchEvent(new CustomEvent('theme:change', {
      detail: { theme: getTheme(), dark: isDark() }
    }));
  };

  const applyTheme = () => {
    root.setAttribute('data-theme', defaultTheme);
  };

  const getTheme = () => root.getAttribute('data-theme') || defaultTheme;

  const setTheme = (_name, options = {}) => {
    applyTheme();
    if (options.persist !== false) {
      try { localStorage.setItem(themeKey, defaultTheme); } catch (error) {}
    }
    emit();
  };

  const applyDark = (value) => {
    root.classList.toggle('dark', !!value);
  };

  const isDark = () => root.classList.contains('dark');

  const setDark = (value, options = {}) => {
    applyDark(value);
    if (options.persist !== false) {
      try { localStorage.setItem(darkKey, value ? '1' : '0'); } catch (error) {}
    }
    emit();
  };

  const init = ({ params } = {}) => {
    applyTheme();
    try { localStorage.setItem(themeKey, defaultTheme); } catch (error) {}

    let darkValue = null;
    if (params && params.has('dark')) {
      darkValue = params.get('dark') === '1';
    } else {
      try {
        const stored = localStorage.getItem(darkKey);
        if (stored === '1') darkValue = true;
        if (stored === '0') darkValue = false;
      } catch (error) {}
    }
    if (darkValue === null) {
      const media = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)');
      darkValue = media ? media.matches : true;
    }
    applyDark(darkValue);
    try { localStorage.setItem(darkKey, darkValue ? '1' : '0'); } catch (error) {}

    const media = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)');
    if (media) {
      const listener = (event) => {
        let stored = null;
        try { stored = localStorage.getItem(darkKey); } catch (error) {}
        if (stored === null) {
          applyDark(event.matches);
          emit();
        }
      };
      if (typeof media.addEventListener === 'function') media.addEventListener('change', listener);
      else if (typeof media.addListener === 'function') media.addListener(listener);
    }

    emit();
  };

  return {
    init,
    setTheme,
    getTheme,
    setDark,
    isDark,
    toggleDark: () => setDark(!isDark()),
    themes: () => [defaultTheme],
  };
})();

window.ThemeManager = ThemeManager;

const ready = (callback) => {
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', callback);
  } else {
    callback();
  }
};

ready(() => {
  const params = new URLSearchParams(window.location.search);
  ThemeManager.init({ params });

  const darkButtons = Array.from(document.querySelectorAll('[data-dark-toggle]'));
  const updateDarkButtons = () => {
    const dark = ThemeManager.isDark();
    darkButtons.forEach((button) => {
      button.setAttribute('aria-pressed', dark ? 'true' : 'false');
      const sun = button.querySelector('[data-dark-icon="sun"]');
      const moon = button.querySelector('[data-dark-icon="moon"]');
      if (sun) sun.classList.toggle('hidden', !dark);
      if (moon) moon.classList.toggle('hidden', dark);
    });
  };

  darkButtons.forEach((button) => {
    button.addEventListener('click', (event) => {
      event.preventDefault();
      ThemeManager.toggleDark();
      updateDarkButtons();
    });
  });

  window.addEventListener('theme:change', () => {
    updateDarkButtons();
  });

  updateDarkButtons();

  const sidebar = document.querySelector('#sidebar');
  const scrim = document.querySelector('[data-sidebar-scrim]');
  const toggleButton = document.querySelector('[data-sidebar-toggle]');
  const closeButton = document.querySelector('[data-sidebar-close]');
  const body = document.body;

  const openSidebar = () => {
    body.classList.add('sidebar-open');
    if (sidebar) sidebar.setAttribute('aria-hidden', 'false');
    if (toggleButton) toggleButton.setAttribute('aria-expanded', 'true');
  };

  const closeSidebar = () => {
    body.classList.remove('sidebar-open');
    if (sidebar) sidebar.setAttribute('aria-hidden', 'true');
    if (toggleButton) toggleButton.setAttribute('aria-expanded', 'false');
  };

  if (toggleButton) {
    toggleButton.addEventListener('click', (event) => {
      event.preventDefault();
      if (body.classList.contains('sidebar-open')) {
        closeSidebar();
      } else {
        openSidebar();
      }
    });
  }

  if (closeButton) {
    closeButton.addEventListener('click', (event) => {
      event.preventDefault();
      closeSidebar();
    });
  }

  if (scrim) {
    scrim.addEventListener('click', (event) => {
      event.preventDefault();
      closeSidebar();
    });
  }

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      closeSidebar();
    }
  });

  const media = window.matchMedia ? window.matchMedia('(min-width: 768px)') : null;
  const handleMedia = (evt) => {
    if (!evt) return;
    if (evt.matches) {
      body.classList.remove('sidebar-open');
      if (sidebar) sidebar.removeAttribute('aria-hidden');
      if (toggleButton) toggleButton.setAttribute('aria-expanded', 'false');
    } else {
      if (sidebar) sidebar.setAttribute('aria-hidden', 'true');
    }
  };

  if (media) {
    if (typeof media.addEventListener === 'function') media.addEventListener('change', handleMedia);
    else if (typeof media.addListener === 'function') media.addListener(handleMedia);
    handleMedia(media);
  } else if (sidebar) {
    sidebar.setAttribute('aria-hidden', 'true');
  }
});

// --- Admin/editor presence indicator ---
try {
    const roleMeta = document.querySelector('meta[name="user-role"]');
    const idMeta = document.querySelector('meta[name="user-id"]');
    const userRole = roleMeta ? roleMeta.getAttribute('content') : null;
    const myId = idMeta ? parseInt(idMeta.getAttribute('content')) : null;
    const shouldJoin = (userRole === 'admin' || userRole === 'editor');
    const shouldRenderBadge = true; // render presence chips in header
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
                badge.title = 'Usuarios en linea (admin/editor)';
                badge.innerHTML = '<span style="width:8px;height:8px;border-radius:50%;background:#9CA3AF;display:inline-block"></span><span style="font-size:12px;color:#6B7280">En linea: 0</span>';
                const actions = header.querySelector('.flex.items-center.gap-2, .flex.items-center.gap-2.shrink-0') || header.lastElementChild;
                if (actions) actions.prepend(badge);
                dot = badge.firstChild;
                label = badge.lastChild;
            }
        }

        let members = [];
        const updateUI = () => {
            if (!shouldRenderBadge || !badge) return;
            const others = members.filter(u => u.id !== myId);
            const list = others.length ? others : members;
            let html = '';
            list.forEach(u => {
                html += '<span style="display:inline-flex;align-items:center;gap:6px;margin-right:6px;">'
                      + '<span style="width:8px;height:8px;border-radius:50%;background:#22C55E;display:inline-block"></span>'
                      + '<span style="font-size:12px;color:#6B7280">' + (u.name ?? '') + (u.role ? ' (' + u.role + ')' : '') + '</span>'
                      + '</span>';
            });
            badge.innerHTML = html;
            badge.title = list.map(u => (u.name ?? '') + (u.role ? ' (' + u.role + ')' : '')).join(', ');
        };

        window.Echo.join('control-panel')
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
                banner(noticeEl, `Este contenido esta siendo editado por ${data.by}.`);
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
