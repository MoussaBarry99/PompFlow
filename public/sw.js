// Service worker minimal : rend l'application installable.
// Aucune mise en cache des pages (les données doivent toujours être à jour).
self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', (event) => event.waitUntil(self.clients.claim()));
self.addEventListener('fetch', () => {});
