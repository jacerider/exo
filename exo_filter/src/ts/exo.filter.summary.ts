/**
 * @file
 * Global exo_aside javascript.
 */

(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.exoFilterSummary = {

    attach: function (context, settings) {
      // 'this' references the form element.
      function triggerSubmit(e) {
        $(this).closest('form').find('.form-actions [type="submit"]').first().trigger('click');
      }

      $('.exo-filter-summary-item', context).once().each(function () {
        var $item = $(this);
        var field = $item.data('exo-filter-summary-field');

        $('.exo-filter-summary-value', this).each(function () {
          $('<i class="icon-regular-times"></i>').appendTo($(this));
        }).on('click', function (e) {
          e.preventDefault();
          var value = $(this).data('exo-filter-summary-value');
          var itemField = $(this).data('exo-filter-summary-field') || field;
          var $field = $(':input[name="' + itemField + '"]');
          switch ($field.get(0).tagName) {
            case 'SELECT':
              $('option[value="' + value + '"]', $field).prop('selected', false);
              triggerSubmit.call($field);
              break;

            case 'INPUT':
              $field.val('');
              triggerSubmit.call($field);
              break;
          }
        });
      });
    }
  };

})(jQuery, Drupal);
