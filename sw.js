const CACHE_NAME = "todo-pwa-v2";
const STATIC_ASSETS = [
  "./",
  "index.php",
  "style.css",
  "app.js",
  "manifest.json",
  "offline.html",
  "icons/icon-192.png",
  "icons/icon-512.png"
];

self.addEventListener("install", function(e) {
  e.waitUntil(
    caches.open(CACHE_NAME).then(function(cache) {
      return cache.addAll(STATIC_ASSETS);
    }).then(function() {
      return self.skipWaiting();
    })
  );
});

self.addEventListener("activate", function(e) {
  e.waitUntil(
    caches.keys().then(function(keys) {
      return Promise.all(
        keys.map(function(key) {
          if (key !== CACHE_NAME) {
            return caches.delete(key);
          }
        })
      );
    }).then(function() {
      return self.clients.claim();
    })
  );
});

self.addEventListener("fetch", function(e) {
  if (e.request.method !== "GET") {
    return;
  }

  const url = new URL(e.request.url);

  if (e.request.mode === "navigate") {
    e.respondWith(
      fetch(e.request)
        .then(function(response) {
          if (response && response.status === 200) {
            const clone = response.clone();
            caches.open(CACHE_NAME).then(function(cache) {
              cache.put(e.request, clone);
            });
          }
          return response;
        })
        .catch(function() {
          return caches.match(e.request, { ignoreSearch: true }).then(function(cached) {
            if (cached) {
              return cached;
            }
            return caches.match("offline.html");
          });
        })
    );
    return;
  }

  if (url.pathname.includes("style.css") || url.pathname.includes("app.js") || url.pathname.includes("icons/") || url.pathname.includes("manifest.json")) {
    e.respondWith(
      caches.match(e.request).then(function(cached) {
        if (cached) {
          return cached;
        }
        return fetch(e.request).then(function(response) {
          const clone = response.clone();
          caches.open(CACHE_NAME).then(function(cache) {
            cache.put(e.request, clone);
          });
          return response;
        });
      })
    );
    return;
  }

  if (url.pathname.includes("uploads/")) {
    e.respondWith(
      caches.match(e.request).then(function(cached) {
        const fetchPromise = fetch(e.request).then(function(networkResponse) {
          if (networkResponse && networkResponse.status === 200) {
            const clone = networkResponse.clone();
            caches.open(CACHE_NAME).then(function(cache) {
              cache.put(e.request, clone);
            });
          }
          return networkResponse;
        }).catch(function() {
          return cached;
        });
        return cached || fetchPromise;
      })
    );
    return;
  }

  e.respondWith(
    caches.match(e.request).then(function(cached) {
      return cached || fetch(e.request);
    })
  );
});
