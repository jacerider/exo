/**
 * @file
 * Global exo_aside javascript.
 */

declare const Litepicker:any;

(function ($, Drupal) {

  'use strict';

  var formatDate = function(date){
    return date.getFullYear() + '-' + String(date.getMonth() + 1) + '-' + String(date.getDate());
  };

  Drupal.behaviors.exoFilterLitepicker = {

    attach: function (context, settings) {
      $(context).find('.exo-litepicker-input-start').once('exo-filter.litepicker').each((index, element) => {
        const $start = $(element);
        const $startContainer = $start.closest('.exo-form-container-exo-litepicker-input');
        const group = $start.data('litepicker-group');
        const $end = $(context).find('.exo-litepicker-input-end[data-litepicker-group="' + group + '"]');
        const $endContainer = $end.closest('.exo-form-container-exo-litepicker-input');
        const $clone = $start.clone().removeAttr('name').removeAttr('id').attr('type', 'text').attr('aria-hidden', 'true').appendTo($end.parent());
        $end.addClass('js-hide');
        $startContainer.addClass('js-hide');

        var startDate = null;
        var endDate = null;
        const startVal = String($start.val());
        const endVal = String($end.val());
        if (startVal) {
          startDate = new Date(startVal + 'T00:00:00');
        }
        if (endVal) {
          endDate = new Date(endVal + 'T23:59:59');
        }

        var picker = new Litepicker({
          element: $clone[0],
          firstDay: 0,
          format: "MMMM D, YYYY",
          numberOfMonths: 2,
          numberOfColumns: 2,
          startDate: startDate,
          endDate: endDate,
          zIndex: 9999,
          selectForward: false,
          selectBackward: false,
          splitView: false,
          singleMode: false,
          showWeekNumbers: false,
          showTooltip: true,
          disableWeekends: true,
          resetButton: true,
          plugins: ['keyboardnav']
        });
        picker.on('render', el => {
          const $ui = $(picker.ui);
          const $skip = $('<a href="" class="skip visually-hidden focusable" tabindex="1">Skip Calendar</a>');
          $skip.on('click', e => {
            e.preventDefault();
            picker.hide();
            $startContainer.removeClass('js-hide').addClass('skip-calendar');
            $end.removeClass('js-hide');
            $endContainer.addClass('skip-calendar');
            $clone.addClass('js-hide');
            $start.trigger('focus');
          });
          $ui.find('.month-item-header').prepend($skip);
        });
        picker.on('selected', (date1, date2) => {
          const start:Date = date1.dateInstance;
          const end:Date = date2.dateInstance;
          $start.val(start.toISOString().split('T')[0]);
          $end.val(end.toISOString().split('T')[0]);
        });
        picker.on('clear:selection', () => {
          $end.val('');
          $start.val('').trigger('change');
        });

      });
    }
  };

})(jQuery, Drupal);
