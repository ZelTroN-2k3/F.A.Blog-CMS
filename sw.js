// sw.js - Service Worker PWA
const CACHE_NAME = 'blog-cms-v1';
const ASSETS_TO_CACHE = [
  '/',
  '/index.php',
  '/assets/css/phpblog.css',
  '/assets/js/phpblog.js',
  '/assets/img/avatar.png',
  '/assets/img/no-image.png'
];

// 1. Installation : On met en cache les fichiers essentiels
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(ASSETS_TO_CACHE);
    })
  );
});

// 2. Activation : On nettoie les vieux caches
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keyList) => {
      return Promise.all(keyList.map((key) => {
        if (key !== CACHE_NAME) {
          return caches.delete(key);
        }
      }));
    })
  );
});

// 3. Interception : Stratégie "Network First" (Réseau en priorité, Cache si hors-ligne)
self.addEventListener('fetch', (event) => {
  // On ne cache pas l'admin ni les requêtes POST
  if (event.request.method !== 'GET' || event.request.url.includes('/admin')) {
    return;
  }

  event.respondWith(
    fetch(event.request)
      .then((response) => {
        // Si on a une réponse valide du réseau, on la clone dans le cache pour la prochaine fois
        const responseClone = response.clone();
        caches.open(CACHE_NAME).then((cache) => {
          cache.put(event.request, responseClone);
        });
        return response;
      })
      .catch(() => {
        // Si le réseau échoue (Offline), on cherche dans le cache
        return caches.match(event.request).then((cachedResponse) => {
            if (cachedResponse) {
                return cachedResponse;
            }
            // Page offline par défaut (Optionnel, ici on renvoie index si possible)
            return caches.match('/index.php');
        });
      })
  );
});