(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.exoListBuilderList = {
    attach: function (context, settings) {
      $('.exo-list-filters-inline', context).once('exo.list.builder.list').each(function () {
        var self = this;
        var show = function () {
          $('.js-hide', self).removeClass('js-hide');
          $('.exo-form-container-js-hide', self).removeClass('exo-form-container-js-hide');
        };
        $(':input', self).on('change', function (e) {
          show();
        }).on('keyup', function (e) {
          show();
        });
      });
    }
  };

  if (Drupal.tableDrag) {
    Drupal.tableDrag.prototype.dropRowOriginal = Drupal.tableDrag.prototype.dropRow;
    Drupal.tableDrag.prototype.dropRow = function (event, self) {
      Drupal.tableDrag.prototype.dropRowOriginal(event, self);
      if (self.changed) {
        var $list = $(self.table.closest('.exo-list'));
        if ($list.length) {
          $list.addClass('exo-list-dragged');
        }
      }
    };

    $.extend(Drupal.theme, {
      tableDragChangedMarker: function tableDragChangedMarker() {
        return "";
      },
      tableDragChangedWarning: function tableDragChangedWarning() {
        return "";
      }
    });
  }

})(jQuery, Drupal);
