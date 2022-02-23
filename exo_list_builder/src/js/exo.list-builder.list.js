(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.exoListBuilderList = {
    attach: function (context, settings) {
      $('.exo-list-filters-inline', context).once('exo.list.builder.list').each(function () {
        var self = this;
        var show = function () {
          $('.js-hide', self).removeClass('js-hide');
          $('.exo-form-container-js-hide', self).removeClass('exo-form-container-js-hide');
        }
        $(':input', self).on('change', function (e) {
          show();
        }).on('keyup', function (e) {
          show();
        });
      });
    }
  };

})(jQuery, Drupal);
