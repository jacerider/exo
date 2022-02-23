(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.exoListBuilderDownload = {
    attach: function (context, settings) {
      $('a[data-auto-download]', context).each(function () {
        var $this = $(this);
        setTimeout(function () {
          window.location = $this.attr('href');
        }, 2000);
      });
    }
  };

})(jQuery, Drupal);
