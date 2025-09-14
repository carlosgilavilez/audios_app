import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Optional Echo init (guarded) — skip if no VITE key/cluster or disabled
try {
  const VITE_KEY = import.meta.env.VITE_PUSHER_APP_KEY;
  const VITE_CLUSTER = import.meta.env.VITE_PUSHER_APP_CLUSTER;
  const disabled = typeof window !== 'undefined' && window.__DISABLE_ECHO__;
  if (VITE_KEY && VITE_CLUSTER && !disabled) {
    const Echo = (await import('laravel-echo')).default;
    const Pusher = (await import('pusher-js')).default;
    window.Pusher = Pusher;
    window.Echo = new Echo({
      broadcaster: 'pusher',
      key: VITE_KEY,
      cluster: VITE_CLUSTER,
      forceTLS: true,
    });
  } else {
    // Prevent errors from code expecting Echo
    window.Echo = null;
  }
} catch (e) {
  // Fail silent if Echo can't be initialized (e.g., production without Vite vars)
  window.Echo = null;
}
