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
