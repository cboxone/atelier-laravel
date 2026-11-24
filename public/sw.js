// Service worker de l'espace technicien.
// Il permet d'installer l'application et d'afficher une page claire hors connexion.
// Il NE met PAS en cache les pages de l'application (données clients) et NE rejoue PAS les saisies hors connexion :
// sans réseau, rien n'est enregistré.
const CACHE = 'tech-shell-v1';
const SHELL = ['/offline.html', '/icons/icon-192.png'];

self.addEventListener('install', (e) => {
  e.waitUntil(caches.open(CACHE).then((c) => c.addAll(SHELL)).then(() => self.skipWaiting()));
});
self.addEventListener('activate', (e) => {
  e.waitUntil(caches.keys().then((ks) => Promise.all(ks.filter((k) => k !== CACHE).map((k) => caches.delete(k)))).then(() => self.clients.claim()));
});
self.addEventListener('fetch', (e) => {
  if (e.request.mode === 'navigate') {
    e.respondWith(fetch(e.request).catch(() => caches.match('/offline.html')));
  }
});
