/**
 * Admin JS — Zan Affiliate Pro
 */
(function ($) {
  'use strict';

  $(document).ready(function () {
    initCharCounters();
    initTemplatePickerHighlight();
    initSerpPreview();
    initCopyLink();
  });

  function initCharCounters() {
    $('.zap-char-counter').each(function () {
      var $counter = $(this);
      var max = parseInt($counter.data('max'));
      var target = $counter.data('target');
      var $field = $('[name="' + target + '"]');

      function update() {
        var len = $field.val().length;
        $counter.find('.current').text(len);
        $counter.toggleClass('over-limit', len > max);
        // Also update SERP preview
        if (target === 'zap_seo_title') {
          $('#zap-serp-title').text($field.val());
        }
        if (target === 'zap_seo_desc') {
          $('#zap-serp-desc').text($field.val());
        }
      }

      $field.on('input', update);
      update();
    });
  }

  function initTemplatePickerHighlight() {
    $(document).on('change', '.zap-template-item input[type="radio"]', function () {
      $('.zap-template-item').removeClass('active');
      $(this).closest('.zap-template-item').addClass('active');
    });
  }

  function initSerpPreview() {
    $('[name="zap_seo_title"]').on('input', function () {
      $('#zap-serp-title').text($(this).val());
    });
    $('[name="zap_seo_desc"]').on('input', function () {
      $('#zap-serp-desc').text($(this).val());
    });
  }

  function initCopyLink() {
    $(document).on('click', '.zap-copy-link', function (e) {
      e.preventDefault();
      var url = $(this).data('url');
      if (!url) return;
      if (navigator.clipboard) {
        navigator.clipboard.writeText(url).then(function () {
          alert('Link copiado: ' + url);
        });
      } else {
        var $ta = $('<textarea/>').val(url).appendTo('body').select();
        document.execCommand('copy');
        $ta.remove();
        alert('Link copiado!');
      }
    });
  }

})(jQuery);
