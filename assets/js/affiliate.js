/**
 * Affiliate JS — Zan Affiliate Pro
 */
(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    initCopyLinks();
    initRatingBars();
    initComparisonHighlight();
  });

  // ── Copy affiliate link ────────────────────────────────────────────────────
  function initCopyLinks() {
    document.querySelectorAll('.zap-copy-link, [data-url]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var url = this.dataset.url;
        if (!url) return;
        navigator.clipboard.writeText(url).then(function () {
          var original = btn.textContent;
          btn.textContent = (window.zapConfig && zapConfig.strings.copied) || 'Copiado!';
          setTimeout(function () { btn.textContent = original; }, 2000);
        }).catch(function () {
          // Fallback
          var ta = document.createElement('textarea');
          ta.value = url;
          document.body.appendChild(ta);
          ta.select();
          document.execCommand('copy');
          document.body.removeChild(ta);
        });
      });
    });
  }

  // ── Animate rating bars on scroll ─────────────────────────────────────────
  function initRatingBars() {
    var bars = document.querySelectorAll('.zap-rating-bar-fill');
    if (!bars.length) return;

    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          var bar = entry.target;
          bar.style.width = bar.style.width || '0%'; // trigger animation
          observer.unobserve(bar);
        }
      });
    }, { threshold: 0.3 });

    bars.forEach(function (bar) {
      var finalWidth = bar.style.width;
      bar.style.width = '0%';
      observer.observe(bar);
      // Store target
      bar.dataset.target = finalWidth;
      setTimeout(function () {
        bar.style.width = finalWidth;
      }, 100);
    });
  }

  // ── Comparison table: highlight on hover ──────────────────────────────────
  function initComparisonHighlight() {
    var tables = document.querySelectorAll('.zap-comparison-table table');
    tables.forEach(function (table) {
      var cols = table.querySelectorAll('th');
      cols.forEach(function (th, i) {
        th.addEventListener('mouseenter', function () {
          table.querySelectorAll('tr').forEach(function (row) {
            var cell = row.cells[i];
            if (cell) cell.classList.add('zap-col-highlight');
          });
        });
        th.addEventListener('mouseleave', function () {
          table.querySelectorAll('.zap-col-highlight').forEach(function (c) {
            c.classList.remove('zap-col-highlight');
          });
        });
      });
    });
  }
})();
