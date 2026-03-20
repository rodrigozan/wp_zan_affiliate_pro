/**
 * Service Worker — Zan Affiliate Pro Push Notifications
 */
'use strict';

var CACHE_NAME = 'zap-cache-v1';
var OFFLINE_URL = '/offline/';

// Install
self.addEventListener('install', function (event) {
  self.skipWaiting();
});

// Activate
self.addEventListener('activate', function (event) {
  event.waitUntil(
    caches.keys().then(function (keys) {
      return Promise.all(
        keys.filter(function (k) { return k !== CACHE_NAME; })
            .map(function (k) { return caches.delete(k); })
      );
    }).then(function () { return self.clients.claim(); })
  );
});

// Push event
self.addEventListener('push', function (event) {
  var data = {};
  try { data = event.data ? event.data.json() : {}; } catch (e) {}

  var title   = data.title   || 'Nova publicação!';
  var body    = data.body    || 'Clique para ler.';
  var icon    = data.icon    || '/wp-content/themes/zan-affiliate-pro/assets/images/icon-192.png';
  var url     = data.url     || '/';
  var badge   = data.badge   || '/wp-content/themes/zan-affiliate-pro/assets/images/badge-72.png';

  event.waitUntil(
    self.registration.showNotification(title, {
      body:    body,
      icon:    icon,
      badge:   badge,
      data:    { url: url },
      tag:     'zap-notification',
      renotify: true,
      vibrate: [200, 100, 200],
    })
  );
});

// Notification click
self.addEventListener('notificationclick', function (event) {
  event.notification.close();
  var targetUrl = (event.notification.data && event.notification.data.url) || '/';

  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (list) {
      for (var i = 0; i < list.length; i++) {
        if (list[i].url === targetUrl && 'focus' in list[i]) {
          return list[i].focus();
        }
      }
      if (clients.openWindow) return clients.openWindow(targetUrl);
    })
  );
});

// Fetch — network-first for HTML, cache-first for assets
self.addEventListener('fetch', function (event) {
  if (event.request.method !== 'GET') return;
  if (event.request.url.includes('/wp-admin/')) return;
  if (event.request.url.includes('/wp-login.php')) return;

  event.respondWith(
    fetch(event.request).catch(function () {
      return caches.match(event.request);
    })
  );
});
