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
      $('.exo-form.is-disabled').each((index, element) => {
        $(element).removeClass('is-disabled');
      });
      $('.exo-form-button-disabled-clone').each((index, element) => {
        $(element).remove();
      });
      $('.exo-form-button-displayed-has-clone').each((index, element) => {
        $(element).removeClass('exo-form-button-displayed-has-clone').show();
      });
      const disableButton = ($button) => {
        const $form = $button.closest('form.exo-form');
        const message = $button.data('exo-form-button-disable-message');
        const $clone = $button.clone().css({
          minWidth: $button.outerWidth() + 'px',
          textAlign: 'center',
        }).addClass('exo-form-button-disabled-clone is-disabled').insertAfter($button);
        if (message) {
          if ($clone.is('input')) {
            $clone.val(message);
          }
          else {
            $clone.text(message);
          }
        }
        $button.addClass('exo-form-button-displayed-has-clone').hide();
        if ($button.data('exo-form-button-disable-form')) {
          $form.addClass('is-disabled');
        }
      };
      let $disableButtons = $('.exo-form-button-disable-on-click', context);
      if ($disableButtons.length) {
        const $form = $disableButtons.closest('form.exo-form');
        let $button;
        $disableButtons.on('mousedown', e => {
          $button = $(e.target);
        });
        if ($disableButtons.filter('[data-once="drupal-ajax"]').length) {
          $(document).once('exo.form.disable').one('ajaxStart', function () {
            setTimeout(function () {
              if ($button && $button.length) {
                disableButton($button);
              }
            });
          });
        }
        $form.once('exo.form.disable').on('submit', e => {
          if ($button && $button.length) {
            disableButton($button);
          }
        });
      }

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

          $(context).find('table').each((index, element) => {
            const $table = $(element);
            if (!$table.closest('form.exo-form').length) {
              $table.addClass('exo-form-table-wrap');
            }
            const $parent = $table.parent();
            if ($table.outerWidth() > $parent.outerWidth() + 2) {
              if (!$parent.hasClass('exo-form-table-overflow')) {
                $table.wrap('<div class="exo-form-table-overflow" />');
              }
            }
            else {
              if ($parent.hasClass('exo-form-table-overflow')) {
                $table.unwrap('.exo-form-table-overflow');
              }
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
