(function ($) {

  class ExoMenuStyleTree extends ExoMenuStyleBase {

    public build() {
      super.build();
      const linkSelector = '.exo-menu-link';
      const $links:JQuery = this.$element.find(linkSelector);

      $links.on('keydown.exo.menu.style.tree', e => {
        const $link = $(e.currentTarget);
        const $parent = $link.closest('li');
        const $expandParent = $parent.parent().closest('.expand');
        const hasDropdown = $parent.hasClass('expanded');
        const isDropdown = $expandParent.length;
        switch (e.which) {
          case 27: // escape
            if (isDropdown) {
              e.preventDefault();
              e.stopPropagation();
              $expandParent.find('> ' + linkSelector).trigger('focus');
            }
            break;
          case 39: // right
            if (!isDropdown) {
              e.preventDefault();
              e.stopPropagation();
              $parent.next('li').find(linkSelector).trigger('focus');
            }
            break;
          case 40: // down
            if (hasDropdown) {
              e.preventDefault();
              e.stopPropagation();
              $parent.find('> .exo-menu-level ' + linkSelector).first().trigger('focus');
            }
            if (isDropdown) {
              e.preventDefault();
              e.stopPropagation();
              $parent.next('li').find(linkSelector).trigger('focus');
            }
            break;
          case 37: // left
            if (!isDropdown) {
              e.preventDefault();
              e.stopPropagation();
              $parent.prev('li').find(linkSelector).trigger('focus');
            }
            break;
          case 38: // up
            if (isDropdown) {
              let $prevParent = $parent.prev('li');
              if (!$prevParent.length) {
                $expandParent.find('> ' + linkSelector).trigger('focus');
              }
              else {
                $prevParent.find(linkSelector).trigger('focus');
              }
            }
            break;
        }
      });
    }
  }

  Drupal.ExoMenuStyles['tree'] = ExoMenuStyleTree;

})(jQuery);
