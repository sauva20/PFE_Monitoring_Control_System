const CACHE_NAME = 'pfe-v3';
const STATIC_ASSETS = [
  '/favicon.svg',
  '/manifest.json',
  '/images/icons/icon-192.png',
  '/images/icons/icon-512.png'
];

// Install: Cache truly static assets only (NEVER HTML pages with dynamic CSRF tokens)
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(STATIC_ASSETS);
    }).then(() => self.skipWaiting())
  );
});

// Activate: Immediately purge all old caches and claim clients
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => {
      return Promise.all(
        keys.map((key) => {
          if (key !== CACHE_NAME) {
            return caches.delete(key);
          }
        })
      );
    }).then(() => self.clients.claim())
  );
});

// Fetch: NEVER intercept HTML navigations or dynamic state
self.addEventListener('fetch', (event) => {
  // Never handle non-GET requests (e.g. POST /login or POST /logout)
  if (event.request.method !== 'GET') {
    return;
  }

  // Never intercept page navigations (HTML pages must always come directly from server with fresh CSRF tokens)
  if (event.request.mode === 'navigate') {
    return;
  }

  const url = new URL(event.request.url);
  if (url.origin !== self.location.origin) {
    return;
  }

  // Only handle static assets
  if (
    url.pathname.startsWith('/images/') ||
    url.pathname.startsWith('/build/') ||
    url.pathname.endsWith('.svg') ||
    url.pathname.endsWith('.png') ||
    url.pathname.endsWith('.woff2') ||
    url.pathname === '/manifest.json'
  ) {
    event.respondWith(
      caches.match(event.request).then((cachedResponse) => {
        if (cachedResponse) {
          return cachedResponse;
        }
        return fetch(event.request).then((networkResponse) => {
          if (networkResponse.status === 200) {
            const responseToCache = networkResponse.clone();
            caches.open(CACHE_NAME).then((cache) => {
              cache.put(event.request, responseToCache);
            });
          }
          return networkResponse;
        });
      })
    );
  }
});
