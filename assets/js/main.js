/**
 * Main JS — Zan Affiliate Pro
 */
(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    initMobileMenu();
    initBackToTop();
    initReadingProgress();
    initFAQ();
    initTOC();
    initLazyImages();
    initStickyHeader();
  });

  // ── Mobile Menu ────────────────────────────────────────────────────────────
  function initMobileMenu() {
    var toggle = document.querySelector('.menu-toggle');
    var nav = document.querySelector('.main-navigation');
    if (!toggle || !nav) return;

    toggle.addEventListener('click', function () {
      var expanded = this.getAttribute('aria-expanded') === 'true';
      this.setAttribute('aria-expanded', String(!expanded));
      nav.classList.toggle('open', !expanded);
    });

    // Close on outside click
    document.addEventListener('click', function (e) {
      if (!nav.contains(e.target) && !toggle.contains(e.target)) {
        nav.classList.remove('open');
        toggle.setAttribute('aria-expanded', 'false');
      }
    });
  }

  // ── Back to Top ────────────────────────────────────────────────────────────
  function initBackToTop() {
    var btn = document.createElement('button');
    btn.className = 'zap-back-to-top';
    btn.setAttribute('aria-label', 'Voltar ao topo');
    btn.innerHTML = '&#8679;';
    document.body.appendChild(btn);

    window.addEventListener('scroll', throttle(function () {
      btn.classList.toggle('visible', window.scrollY > 400);
    }, 100));

    btn.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  // ── Reading Progress ───────────────────────────────────────────────────────
  function initReadingProgress() {
    var entry = document.querySelector('.entry-content');
    if (!entry) return;

    var bar = document.createElement('div');
    bar.className = 'zap-progress-bar';
    document.body.prepend(bar);

    window.addEventListener('scroll', throttle(function () {
      var rect = entry.getBoundingClientRect();
      var total = entry.offsetHeight - window.innerHeight;
      var scrolled = -rect.top;
      var pct = Math.min(100, Math.max(0, (scrolled / total) * 100));
      bar.style.width = pct + '%';
    }, 50));
  }

  // ── FAQ Accordion ──────────────────────────────────────────────────────────
  function initFAQ() {
    document.querySelectorAll('.zap-faq-question').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var expanded = this.getAttribute('aria-expanded') === 'true';
        var answer = this.nextElementSibling;

        // Close all in same FAQ
        var parent = this.closest('.zap-faq');
        if (parent) {
          parent.querySelectorAll('.zap-faq-question').forEach(function (q) {
            q.setAttribute('aria-expanded', 'false');
            var a = q.nextElementSibling;
            if (a) a.hidden = true;
            var icon = q.querySelector('.zap-faq-icon');
            if (icon) icon.textContent = '+';
          });
        }

        if (!expanded) {
          this.setAttribute('aria-expanded', 'true');
          if (answer) answer.hidden = false;
          var icon = this.querySelector('.zap-faq-icon');
          if (icon) icon.textContent = '−';
        }
      });
    });
  }

  // ── TOC Toggle ─────────────────────────────────────────────────────────────
  function initTOC() {
    var toggleBtn = document.querySelector('.zap-toc-toggle');
    if (!toggleBtn) return;
    var nav = document.querySelector('.zap-toc-nav');
    if (!nav) return;

    toggleBtn.addEventListener('click', function () {
      var hidden = nav.hidden;
      nav.hidden = !hidden;
      this.textContent = hidden ? '−' : '+';
    });

    // Highlight current section
    var headings = document.querySelectorAll('.entry-content h2[id], .entry-content h3[id]');
    var tocLinks = document.querySelectorAll('.zap-toc-item a');
    if (!headings.length || !tocLinks.length) return;

    window.addEventListener('scroll', throttle(function () {
      var scrollPos = window.scrollY + 100;
      var current = '';
      headings.forEach(function (h) {
        if (h.offsetTop <= scrollPos) current = h.id;
      });
      tocLinks.forEach(function (a) {
        a.parentElement.classList.toggle(
          'zap-toc-active',
          a.getAttribute('href') === '#' + current
        );
      });
    }, 100));
  }

  // ── Native lazy load fallback ──────────────────────────────────────────────
  function initLazyImages() {
    if ('loading' in HTMLImageElement.prototype) return; // native support

    var imgs = document.querySelectorAll('img[loading="lazy"]');
    if (!imgs.length) return;

    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          var img = entry.target;
          if (img.dataset.src) img.src = img.dataset.src;
          observer.unobserve(img);
        }
      });
    }, { rootMargin: '200px' });

    imgs.forEach(function (img) { observer.observe(img); });
  }

  // ── Sticky Header shadow ───────────────────────────────────────────────────
  function initStickyHeader() {
    var header = document.querySelector('.site-header');
    if (!header) return;
    window.addEventListener('scroll', throttle(function () {
      header.classList.toggle('scrolled', window.scrollY > 10);
    }, 100));
  }

  // ── Throttle ──────────────────────────────────────────────────────────────
  function throttle(fn, delay) {
    var last = 0;
    return function () {
      var now = Date.now();
      if (now - last >= delay) { last = now; fn.apply(this, arguments); }
    };
  }

  // ── Expose utilities globally for other scripts ────────────────────────────
  window.zapUtils = { throttle: throttle };
})();
