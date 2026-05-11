const CACHE_NAME = 'trackpos-v1';
const OFFLINE_URLS = [
    '/',
    '/pos',
    '/dashboard',
];

// Install event - cache static assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(OFFLINE_URLS);
        })
    );
    self.skipWaiting();
});

// Activate event - clean old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    self.clients.claim();
});

// Fetch event - network first, fallback to cache
self.addEventListener('fetch', (event) => {
    // Skip non-GET requests
    if (event.request.method !== 'GET') return;
    
    // Skip API calls - they should go to network
    if (event.request.url.includes('/api/')) return;
    
    event.respondWith(
        fetch(event.request)
            .then((response) => {
                // Clone response for caching
                const responseClone = response.clone();
                caches.open(CACHE_NAME).then((cache) => {
                    cache.put(event.request, responseClone);
                });
                return response;
            })
            .catch(() => {
                // Network failed, try cache
                return caches.match(event.request).then((response) => {
                    return response || caches.match('/');
                });
            })
    );
});

// Listen for sync requests from main app
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SYNC_SALES') {
        event.waitUntil(syncPendingSales());
    }
});

async function syncPendingSales() {
    // Get pending sales from IndexedDB or fetch from API
    try {
        const clients = await self.clients.matchAll();
        clients.forEach(client => {
            client.postMessage({ type: 'SYNC_COMPLETE' });
        });
    } catch (error) {
        console.error('Sync failed:', error);
    }
}