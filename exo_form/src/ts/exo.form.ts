(function ($, Drupal) {

  'use strict';

  let $scope:JQuery;

  Drupal.behaviors.exoForm = {
    once: false,
    attach: function (context) {
      $scope = $('form.exo-form:visible');

      // Container inline has been replaced with exo-form-inline.
      $('.exo-form').once('exo.form.init').each((index, element) => {
        const $form = $(element);
        if (!$('> *:visible', $form).length) {
          // Add empty div so that we have correct margin
          $form.append('<div></div>');
        }
        $form.removeClass('is-disabled');
        $form.find('.container-inline').removeClass('container-inline');
        $form.find('.form--inline').removeClass('form--inline').addClass('exo-form-inline');
        $form.find('.form-items-inline').removeClass('form-items-inline');
      });

      // Disable on click.
      const $buttons = $('.exo-form-button-disable-on-click');
      $('.exo-form-button-disable-on-click.is-disabled').each((index, element) => {
        const $button = $(element);
        const $form = $button.closest('form.exo-form');
        $form.removeClass('is-disabled');
        $button.removeClass('is-disabled');
        if ($button.data('exo-form-button-original-message')) {
          $button.text($button.data('exo-form-button-original-message'));
        }
      });
      const disableButton = (e) => {
        const $button = $(e.target);
        const $form = $button.closest('form.exo-form');
        const message = $button.data('exo-form-button-disable-message');
        if (message) {
          $button.css({
            minWidth: $button.outerWidth() + 'px',
            textAlign: 'center',
          });
          $button.data('exo-form-button-original-message', $button.text());
          $button.text(message);
        }
        $button.addClass('is-disabled');
        if ($button.data('exo-form-button-disable-form')) {
          $form.addClass('is-disabled');
        }
      };
      $('.exo-form-button-disable-on-click', context).once('exo.form.disable').on('mousedown', e => {
        const $button = $(e.target);
        setTimeout(() => {
          if (!$button.hasClass('is-disabled')) {
            disableButton(e);
          }
        }, 100);
      }).on('click', disableButton);

      // Form styling.
      $(context).find('td .dropbutton-wrapper').once('exo.form.td.compact').each((index, element) => {
        setTimeout(() => {
          $(element).css('min-width', $(element).outerWidth());
        });
      }).parent().addClass('exo-form-table-compact');
      $(context).find('td.views-field-changed, td.views-field-created').once('exo.form.td.compact').addClass('exo-form-table-compact');
      $(context).find('td > .exo-icon').once('exo.form.td.compact').each((index, element) => {
        const $td = $(element).parent();
        if ($td.children(':not(.exo-icon-label)').length === 1) {
          $td.addClass('exo-form-table-compact');
        }
      });
      $(context).find('table').once('exo.form.table').each((index, element) => {
        const $table = $(element);
        if (!$table.closest('form.exo-form').length) {
          $table.addClass('exo-form-table-wrap');
        }
        if ($table.outerWidth() > $table.parent().outerWidth() + 2) {
          $table.wrap('<div class="exo-form-table-overflow" />');
        }
      });

      // Webform support.
      $(context).find('.webform-tabs').once('exo.form.refresh').each(function (e) {
        $(this).addClass('horizontal-tabs').wrap('<div class="exo-form-horizontal-tabs exo-form-element exo-form-element-js" />');
        $(this).find('.item-list ul').addClass('horizontal-tabs-list').find('> li').addClass('horizontal-tab-button');
        $(this).find('> .webform-tab').addClass('horizontal-tabs-pane').wrapAll('<div class="horizontal-tabs-panes" />');
      }).on('tabsbeforeactivate', function (e, ui) {
        ui.oldPanel.hide();
        ui.newPanel.show();
      });

      // Hide empty containers.
      $(context).find('.exo-form-container-hide').each(function () {
        if ($(this).text().trim().length) {
          $(this).removeClass('exo-form-container-hide');
        }
      });

      // Process each exo form.
      $scope.once('exo.form').each((index, element) => {
        const $localscope = $(element);
        // Wrap pad.
        $localscope.filter('.exo-form-wrap').each((index, element) => {
          if ($(element).html().trim()[0] !== '<') {
            $(element).addClass('exo-form-wrap-pad');
          }
        });
        // Support utilization of a parent which defines an exo theme.
        const $parentTheme = $localscope.closest('[data-exo-theme]');
        if ($parentTheme.length) {
          $localscope.removeClass(function (index, className) {
            return (className.match (/(^|\s)exo-form-theme-\S+/g) || []).join(' ');
          }).addClass('exo-form-theme-' + $parentTheme.data('exo-theme'));
        }
      });

      // Perform only once.
      if (!this.once) {
        this.once = true;
        function watchInline() {
          $scope.find('.exo-form-inline').each((index, element) => {
            const $element = $(element);
            let innerWidth = 0;
            $element.removeClass('exo-form-inline-stack');
            $element.find('> *:visible').each((index, el) => {
              innerWidth += $(el).outerWidth();
            });
            if (innerWidth > Drupal.Exo.$window.width()) {
              $element.addClass('exo-form-inline-stack');
            }
          });
        }

        Drupal.Exo.addOnResize('exo.form.core', watchInline);
        Drupal.Exo.event('ready').on('exo.form', e => {
          Drupal.Exo.event('ready').off('exo.form');
          watchInline();
        });
      }
    }
  };

})(jQuery, Drupal);
