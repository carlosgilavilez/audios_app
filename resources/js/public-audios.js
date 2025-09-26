document.addEventListener('DOMContentLoaded', () => {
  const root = document.querySelector('[data-public-audios]');
  if (!root) return;

  const body = document.body;
  const params = new URLSearchParams(window.location.search);
  const shell = root.querySelector('[data-layout-shell]');

  const storage = {
    get(key) {
      try {
        return localStorage.getItem(key);
      } catch (error) {
        return null;
      }
    },
    set(key, value) {
      try {
        localStorage.setItem(key, value);
      } catch (error) {
        /* ignore */
      }
    },
  };

  const keys = {
    view: 'public-audios-view',
    width: 'public-audios-preview-width',
  };

  let previewBar = root.querySelector('[data-preview-bar]');
  const isEmbed = root.dataset.embed === '1';

  if (isEmbed || window.self !== window.top) {
    body.setAttribute('data-embed-detected', '1');
    if (previewBar) {
      previewBar.remove();
      previewBar = null;
      params.delete('wp_width');
    }
  }

  const previewButtons = previewBar
    ? Array.from(previewBar.querySelectorAll('[data-preview-size-option]'))
    : [];
  const previewLabel = previewBar
    ? previewBar.querySelector('[data-preview-size]')
    : null;
  const allowedWidths = [414, 820, 1280];
  let previewWidth = parseInt(root.dataset.previewWidth || '1280', 10);
  if (!allowedWidths.includes(previewWidth)) {
    previewWidth = 1280;
  }

  if (previewBar) {
    if (params.has('wp_width')) {
      const candidate = parseInt(params.get('wp_width') || '0', 10);
      if (allowedWidths.includes(candidate)) {
        previewWidth = candidate;
      }
    } else {
      const storedWidth = storage.get(keys.width);
      if (storedWidth) {
        const saved = parseInt(storedWidth, 10);
        if (allowedWidths.includes(saved)) {
          previewWidth = saved;
        }
      }
    }
  }

  const viewButtons = Array.from(root.querySelectorAll('[data-view-option]'));
  const panels = Array.from(root.querySelectorAll('[data-view-panel]'));

  let currentView = params.get('view')
    || root.dataset.initialView
    || storage.get(keys.view)
    || 'table';
  if (!['table', 'cards'].includes(currentView)) {
    currentView = 'table';
  }

  const syncUrl = () => {
    params.set('view', currentView);

    if (previewBar) {
      params.set('wp_width', String(previewWidth));
    } else {
      params.delete('wp_width');
    }

    if (window.ThemeManager && typeof window.ThemeManager.isDark === 'function') {
      params.set('dark', window.ThemeManager.isDark() ? '1' : '0');
    }

    const query = params.toString();
    const url = query
      ? `${window.location.pathname}?${query}`
      : window.location.pathname;
    window.history.replaceState({}, '', url);
  };

  const applyShellBreakpoint = (width) => {
    if (!shell || !Number.isFinite(width)) return;

    shell.classList.remove('is-desktop', 'is-tablet', 'is-mobile');
    if (width >= 1024) {
      shell.classList.add('is-desktop');
    } else if (width >= 768) {
      shell.classList.add('is-tablet');
    } else {
      shell.classList.add('is-mobile');
    }
  };

  const applyPreviewWidth = (width, { persist = true, skipHistory = false } = {}) => {
    if (!previewBar) return;

    if (!allowedWidths.includes(width)) {
      width = 1280;
    }
    previewWidth = width;

    previewButtons.forEach((button) => {
      const value = parseInt(button.getAttribute('data-preview-size-option') || '0', 10);
      const active = value === width;
      button.setAttribute('aria-pressed', active ? 'true' : 'false');
      button.classList.toggle('is-active', active);
      if (active && previewLabel) {
        const label = button.getAttribute('data-preview-size-label');
        if (label) {
          previewLabel.textContent = label;
        }
      }
    });

    document.body.style.setProperty('--wp-preview-width', `${width}px`);
    document.body.setAttribute('data-wp-width', String(width));

    if (persist) {
      storage.set(keys.width, String(width));
    }
    if (!skipHistory) {
      syncUrl();
    }

    applyShellBreakpoint(width);
  };

  const applyView = (view, { persist = true, skipHistory = false } = {}) => {
    if (!['table', 'cards'].includes(view)) {
      view = 'table';
    }
    currentView = view;

    panels.forEach((panel) => {
      const target = panel.getAttribute('data-view-panel');
      const active = target === view;
      panel.classList.toggle('hidden', !active);
      panel.classList.toggle('block', active);
      if (active) {
        panel.setAttribute('data-view-active', 'true');
      } else {
        panel.removeAttribute('data-view-active');
      }
    });

    viewButtons.forEach((button) => {
      const value = button.getAttribute('data-view-option');
      const isActive = value === view;
      button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
      button.classList.toggle('is-active', isActive);
    });

    if (persist) {
      storage.set(keys.view, view);
    }
    if (!skipHistory) {
      syncUrl();
    }

    if (window.Player && typeof window.Player.bind === 'function') {
      window.Player.bind();
    }
  };

  const drawer = root.querySelector('[data-filters-dialog]');
  const drawerPanel = drawer
    ? drawer.querySelector('[data-filters-panel]')
    : null;
  const drawerScrim = drawer
    ? drawer.querySelector('[data-filters-scrim]')
    : null;
  const openFiltersButton = root.querySelector('[data-filters-toggle]');
  const closeFiltersButton = root.querySelector('[data-filters-close]');
  const focusableSelector = 'a[href], button:not([disabled]), select:not([disabled]), textarea:not([disabled]), input:not([disabled]):not([type="hidden"]), [tabindex]:not([tabindex="-1"])';
  let lastFocus = null;

  const trapFocus = (event) => {
    if (!drawerPanel || event.key !== 'Tab') return;

    const focusable = Array
      .from(drawerPanel.querySelectorAll(focusableSelector))
      .filter((el) => el.offsetParent !== null);
    if (!focusable.length) return;

    const first = focusable[0];
    const last = focusable[focusable.length - 1];

    if (event.shiftKey && document.activeElement === first) {
      event.preventDefault();
      last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
      event.preventDefault();
      first.focus();
    }
  };

  const setDrawerOpen = (open) => {
    if (!drawer || !drawerPanel) return;

    if (open) {
      lastFocus = document.activeElement && typeof document.activeElement.focus === 'function'
        ? document.activeElement
        : null;

      drawer.classList.remove('hidden');
      drawer.classList.remove('pointer-events-none');
      body.classList.add('filters-open');

      requestAnimationFrame(() => {
        drawerPanel.classList.remove('translate-x-full');
        const focusTarget = drawerPanel.querySelector(focusableSelector);
        if (focusTarget) {
          focusTarget.focus({ preventScroll: true });
        }
      });

      drawer.addEventListener('keydown', trapFocus);
      if (openFiltersButton) {
        openFiltersButton.setAttribute('aria-expanded', 'true');
      }
    } else {
      drawerPanel.classList.add('translate-x-full');
      drawer.classList.add('pointer-events-none');
      body.classList.remove('filters-open');
      drawer.removeEventListener('keydown', trapFocus);

      setTimeout(() => {
        drawer.classList.add('hidden');
      }, 200);

      if (openFiltersButton) {
        openFiltersButton.setAttribute('aria-expanded', 'false');
      }

      if (lastFocus && typeof lastFocus.focus === 'function') {
        lastFocus.focus({ preventScroll: true });
      }
    }
  };

  if (openFiltersButton) {
    openFiltersButton.addEventListener('click', (event) => {
      event.preventDefault();
      setDrawerOpen(true);
    });
  }

  if (closeFiltersButton) {
    closeFiltersButton.addEventListener('click', (event) => {
      event.preventDefault();
      setDrawerOpen(false);
    });
  }

  if (drawerScrim) {
    drawerScrim.addEventListener('click', (event) => {
      event.preventDefault();
      setDrawerOpen(false);
    });
  }

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && drawer && !drawer.classList.contains('hidden')) {
      setDrawerOpen(false);
    }
  });

  viewButtons.forEach((button) => {
    button.addEventListener('click', (event) => {
      event.preventDefault();
      applyView(button.getAttribute('data-view-option'));
    });
  });

  previewButtons.forEach((button) => {
    button.addEventListener('click', (event) => {
      event.preventDefault();
      const value = parseInt(button.getAttribute('data-preview-size-option') || '0', 10);
      applyPreviewWidth(value);
    });
  });

  if (previewBar) {
    applyPreviewWidth(previewWidth, { persist: false, skipHistory: true });
  } else if (shell) {
    const computeWidth = () => {
      const width = Math.max(window.innerWidth || 0, document.documentElement.clientWidth || 0);
      applyShellBreakpoint(width);
    };
    computeWidth();
    window.addEventListener('resize', computeWidth);
  }

  applyView(currentView, { persist: false, skipHistory: true });
  syncUrl();

  if (window.Player && typeof window.Player.bind === 'function') {
    window.Player.bind();
  }

  window.addEventListener('theme:change', () => {
    syncUrl();
  });
});
