/* CAT CPNS PWA Service Worker
 *
 * Goals:
 * - Installable PWA on Android (A2HS) while still using the same backend/domain.
 * - Keep UI changes in sync with hosting: do NOT aggressively cache HTML pages.
 * - Cache only small, safe same-origin assets + provide offline fallback page.
 */

const CACHE_VERSION = 'catasn-pwa-v20261008-4';
const PRECACHE = [
  '/offline.html',
  '/manifest.webmanifest?v=20261008-4',
  '/icons/icon-192.png?v=20261008-4',
  '/icons/icon-512.png?v=20261008-4',
  '/icons/maskable-icon-192.png?v=20261008-4',
  '/icons/maskable-icon-512.png?v=20261008-4',
  '/icons/favicon.png?v=20261008-4',
  '/favicon.ico'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    (async () => {
      const cache = await caches.open(CACHE_VERSION);
      await cache.addAll(PRECACHE);
      self.skipWaiting();
    })()
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    (async () => {
      const keys = await caches.keys();
      await Promise.all(keys.map((k) => (k === CACHE_VERSION ? Promise.resolve() : caches.delete(k))));
      await self.clients.claim();

      const clients = await self.clients.matchAll({ type: 'window', includeUncontrolled: true });
      clients.forEach((client) => {
        client.postMessage({ type: 'SW_ACTIVATED', version: CACHE_VERSION });
      });
    })()
  );
});

function isSameOrigin(url) {
  try { return new URL(url).origin === self.location.origin; } catch (e) { return false; }
}

async function staleWhileRevalidate(request) {
  const cache = await caches.open(CACHE_VERSION);
  const cached = await cache.match(request);

  const fetchPromise = fetch(request)
    .then((res) => {
      // Cache only successful same-origin GET responses.
      if (res && res.ok) cache.put(request, res.clone());
      return res;
    })
    .catch(() => null);

  return cached || (await fetchPromise);
}

async function networkFirstNavigation(request) {
  try {
    // Avoid caching HTML navigations: always try network for latest UI.
    return await fetch(request);
  } catch (e) {
    const cache = await caches.open(CACHE_VERSION);
    const offline = await cache.match('/offline.html');
    return offline || new Response('Offline', { status: 503, headers: { 'Content-Type': 'text/plain' } });
  }
}

self.addEventListener('fetch', (event) => {
  const req = event.request;

  // Only handle GET.
  if (req.method !== 'GET') return;

  const url = new URL(req.url);

  // Navigation requests (HTML pages): network-first, offline fallback.
  if (req.mode === 'navigate' || (req.destination === 'document' && req.headers.get('accept')?.includes('text/html'))) {
    event.respondWith(networkFirstNavigation(req));
    return;
  }

  // Only cache same-origin assets.
  if (!isSameOrigin(url.href)) return;

  // Cache safe asset types.
  if (['style', 'script', 'image', 'font'].includes(req.destination)) {
    event.respondWith(staleWhileRevalidate(req));
    return;
  }

  // Default: passthrough.
});

self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
});
