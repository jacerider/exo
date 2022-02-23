(function ($, _, Drupal) {

  /**
   * eXo Alchemist admin behavior.
   */
  Drupal.behaviors.exoAlchemistAdminChoose = {
    attach: function(context) {
      $('.exo-component-choose', context)
      .once('exo.alchemist.choose')
      .each((index, element) => {
        const $element = $(element);
        const $list = $element.find('.exo-component-selection');
        const Shuffle = window.Shuffle;
        const shuffleInstance = new Shuffle($list[0], {
          itemSelector: '.exo-component-select',
        });
        $list.data('Shuffle', shuffleInstance);
        $element.find('.exo-component-filter').on('click', e => {
          $element.find('.exo-component-categories').toggleClass('active');
        });

        if (typeof Drupal.ExoModal !== 'undefined') {
          Drupal.ExoModal.event('opened').on('exo.alchemist', (modal:ExoModal) => {
            $element.imagesLoaded(() => {
              shuffleInstance.update();
            });
          });
        }
        else {
          $element.imagesLoaded(() => {
            shuffleInstance.update();
          });
        }

        $element.closest('.exo-modal').on('fullscreen', e => {
          shuffleInstance.update();
        });

        $element.find('.exo-component-search').on('keyup', e => {
          const searchText = $(e.target).val().toString().toLowerCase();
          shuffleInstance.filter((element, shuffle) => {
            // If there is a current filter applied, ignore elements that don't match it.
            if (shuffle.group !== Shuffle.ALL_ITEMS) {
              // Get the item's groups.
              const groups = JSON.parse(element.getAttribute('data-groups'));
              const isElementInCurrentGroup = groups.indexOf(shuffle.group) !== -1;
              // Only search elements in the current group
              if (!isElementInCurrentGroup) {
                return false;
              }
            }
            const titleElement = element.querySelector('.exo-component-label');
            const titleText = titleElement.textContent.toLowerCase().trim();
            return titleText.indexOf(searchText) !== -1;
          });
        });

        $element.find('.exo-component-category-button').on('click', e => {
          e.preventDefault();
          const $btn = $(e.currentTarget);
          let filterLabel = $btn.text();
          const isActive = $btn.hasClass('active');
          const btnGroup = $btn.data('group');
          $element.find('.exo-component-category-button.active').removeClass('active')

          let filterGroup;
          if (isActive) {
            $btn.removeClass('active');
            filterLabel = Drupal.t('All');
            filterGroup = Shuffle.ALL_ITEMS;
          } else {
            $btn.addClass('active');
            filterGroup = btnGroup;
          }

          shuffleInstance.filter(filterGroup);
          $element.find('.exo-component-filter').text(filterLabel);
          $element.find('.exo-component-categories').toggleClass('active');
        });
      });
    },

    detach: function detach(context, settings, trigger) {
      if (trigger === 'unload') {
        Drupal.Exo.$window.off('exo-modal:onOpened.alchemist');
        $('.exo-component-selection', context).each((index, element) => {
          var Shuffle = $(element).data('Shuffle');
          if (Shuffle) {
            Shuffle.destroy();
          }
        });
      }
    }
  }

})(jQuery, _, Drupal);
