/**
 * Push Notification client — Zan Affiliate Pro
 */
(function () {
  'use strict';

  if (!('serviceWorker' in navigator) || !('PushManager' in window)) return;
  if (!window.zapPush || !zapPush.enabled) return;

  function urlBase64ToUint8Array(base64String) {
    var padding = '='.repeat((4 - base64String.length % 4) % 4);
    var base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    var rawData = atob(base64);
    return Uint8Array.from(rawData.split('').map(function (c) { return c.charCodeAt(0); }));
  }

  function sendSubscriptionToServer(subscription, action) {
    var keys = subscription.getKey ? {
      p256dh: btoa(String.fromCharCode.apply(null, new Uint8Array(subscription.getKey('p256dh')))),
      auth:   btoa(String.fromCharCode.apply(null, new Uint8Array(subscription.getKey('auth')))),
    } : {};

    return fetch(zapPush.ajaxUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({
        action:   'zap_push_' + action,
        nonce:    zapPush.nonce,
        endpoint: subscription.endpoint,
        p256dh:   keys.p256dh || '',
        auth:     keys.auth   || '',
      }),
    });
  }

  navigator.serviceWorker.register(zapPush.swUrl)
    .then(function (registration) {
      return registration.pushManager.getSubscription().then(function (sub) {
        // Create bell UI
        var bell = document.createElement('button');
        bell.className = 'zap-push-bell';
        bell.setAttribute('aria-label', sub ? zapPush.strings.unsubscribe : zapPush.strings.subscribe);
        bell.innerHTML = sub ? '🔔' : '🔕';
        document.body.appendChild(bell);

        bell.addEventListener('click', function () {
          if (Notification.permission === 'denied') {
            alert(zapPush.strings.blocked);
            return;
          }

          registration.pushManager.getSubscription().then(function (existing) {
            if (existing) {
              // Unsubscribe
              existing.unsubscribe().then(function () {
                sendSubscriptionToServer(existing, 'unsubscribe');
                bell.innerHTML = '🔕';
                bell.setAttribute('aria-label', zapPush.strings.subscribe);
              });
            } else {
              // Subscribe
              registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(zapPush.vapidPublic),
              }).then(function (newSub) {
                sendSubscriptionToServer(newSub, 'subscribe');
                bell.innerHTML = '🔔';
                bell.setAttribute('aria-label', zapPush.strings.unsubscribe);
              }).catch(function (err) {
                console.warn('[ZAP Push] Subscribe failed:', err);
              });
            }
          });
        });
      });
    })
    .catch(function (err) {
      console.warn('[ZAP Push] SW registration failed:', err);
    });
})();
