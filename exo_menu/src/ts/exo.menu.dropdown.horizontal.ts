(function ($) {

  class ExoMenuStyleDropdownHorizontal extends ExoMenuStyleBase {
    protected defaults:ExoSettingsGroupInterface = {
      // icon used to signify menu items that will open a submenu
      itemIcon: '',
      // make secondary links expandable.
      expandable: true,
      // unbind events.
      unbindFirst: false,
      transitionIn: 'expandInY', // comingIn, bounceInDown, bounceInUp, fadeInDown, fadeInUp, fadeInLeft, fadeInRight, flipInX
      transitionOut: 'expandOutY', // comingOut, bounceOutDown, bounceOutUp, fadeOutDown, fadeOutUp, , fadeOutLeft, fadeOutRight, flipOutX
    }
    protected linkSelector:string = '.exo-menu-link';

    public build() {
      super.build();
      const isExpandable:boolean = this.get('expandable');
      const isFirstUnbound: boolean = this.get('unbindFirst');
      const $links:JQuery = this.$element.find(this.linkSelector);
      this.$element.attr('role', 'menubar');

      $links.each((i, element) => {
        const $link = $(element);
        const $parent = $link.closest('li');
        const hasDropdown = $parent.hasClass('expanded');
        if (hasDropdown) {
          $link.attr('aria-haspopup', 'true');
          $link.attr('aria-expanded', 'false');
        }
      }).on('keydown.exo.menu.style.dropdown', e => {
        const $link = $(e.currentTarget);
        const $parent = $link.closest('li');
        const $expandParent = $parent.parent().closest('.expand');
        const hasDropdown = $parent.hasClass('expanded');
        const isDropdown = $expandParent.length;
        let $focusParent;
        switch (e.which) {
          case 27: // escape
            if (isDropdown) {
              e.preventDefault();
              e.stopPropagation();
              this.hide($expandParent);
              $expandParent.find('> ' + this.linkSelector + ':first').trigger('focus');
            }
            break;
          case 39: // right
            if (!isDropdown) {
              e.preventDefault();
              e.stopPropagation();
              this.hide($parent);
              $focusParent = $parent.next('li');
              $focusParent.find(this.linkSelector + ':first').trigger('focus');
              if ($focusParent.hasClass('expanded')) {
                this.show($focusParent);
              }
            }
            break;
          case 40: // down
            if (hasDropdown) {
              e.preventDefault();
              e.stopPropagation();
              this.show($parent);
              $parent.find('> .exo-menu-level ' + this.linkSelector + ':first').trigger('focus');
            }
            if (isDropdown) {
              e.preventDefault();
              e.stopPropagation();
              $parent.next('li').find(this.linkSelector + ':first').trigger('focus');
            }
            break;
          case 37: // left
            if (!isDropdown) {
              e.preventDefault();
              e.stopPropagation();
              this.hide($parent);
              $focusParent = $parent.prev('li');
              $focusParent.find(this.linkSelector + ':first').trigger('focus');
              if ($focusParent.hasClass('expanded')) {
                this.show($focusParent);
              }
            }
            break;
          case 38: // up
            if (isDropdown) {
              let $prevParent = $parent.prev('li');
              if (!$prevParent.length) {
                this.hide($expandParent);
                $expandParent.find('> ' + this.linkSelector).first().trigger('focus');
              }
              else {
                $prevParent.find(this.linkSelector).first().trigger('focus');
              }
            }
            break;
        }
      });

      if (this.get('itemIcon')) {
        if (isExpandable) {
          this.$element.find('.expanded > a').append(this.get('itemIcon'));
        }
        else {
          this.$element.find('.level-0 > ul > .expanded > a').append(this.get('itemIcon'));
        }
      }

      if (isFirstUnbound) {
        this.$element.find('.level-0 > ul > .expanded.active-trail').addClass('no-event').addClass('expand');
        this.$element.find('.level-1 > ul > .expanded').on('mouseenter.exo.menu.style.dropdown', e => {
          const $target = $(e.currentTarget);
          clearTimeout($target.data('timeout'));
          let timeout = setTimeout(() => {
            Drupal.Exo.getBodyElement().addClass('exo-menu-expanded');
            this.show($(e.currentTarget));
          }, 200);
          $target.data('timeout', timeout);
        }).on('mouseleave.exo.menu.style.dropdown', e => {
          const $target = $(e.currentTarget);
          clearTimeout($target.data('timeout'));
          Drupal.Exo.getBodyElement().removeClass('exo-menu-expanded');
          this.hide($(e.currentTarget));
        });
      }
      else {
        this.$element.find('.level-0 > ul > .expanded')
        .on('mouseenter.exo.menu.style.dropdown', e => {
          const $target = $(e.currentTarget);
          clearTimeout($target.data('timeout'));
          let timeout = setTimeout(() => {
            Drupal.Exo.getBodyElement().addClass('exo-menu-expanded');
            this.show($(e.currentTarget));
          }, 200);
          $target.data('timeout', timeout);
        }).on('mouseleave.exo.menu.style.dropdown', e => {
          const $target = $(e.currentTarget);
          clearTimeout($target.data('timeout'));
          Drupal.Exo.getBodyElement().removeClass('exo-menu-expanded');
          this.hide($(e.currentTarget));
        });
      }

      if (isExpandable) {
        this.$element.find('.level-1 .expanded > a').on('click.exo.menu.style.dropdown', e => {
          e.preventDefault();
          this.toggle($(e.target).closest('.expanded'), false);
        });
      }
      else {
        this.$element.find('.level-1 .expanded').addClass('expand');
      }
    }

    protected toggle($item:JQuery, animate?:boolean) {
      animate = animate !== false;
      if ($item.hasClass('expand')) {
        this.hide($item, animate);
      }
      else {
        this.show($item, animate);
      }
    }

    protected show($item:JQuery, animate?:boolean) {
      const $submenu = $item.find('> .exo-menu-level');
      animate = animate !== false;
      if ($submenu.length && !$item.hasClass('expand')) {
        $item.addClass('expand');
        $item.find(this.linkSelector + ':first').attr('aria-expanded', 'true');
        if (animate && this.get('transitionIn') !== '' && Drupal.Exo.animationEvent !== undefined) {
          $submenu.off(Drupal.Exo.animationEvent + '.exo.menu.hide');
          $submenu.removeClass('exo-animate-' + this.get('transitionOut'));
          $submenu.addClass('exo-animate-' + this.get('transitionIn'));
          $submenu.one(Drupal.Exo.animationEvent + '.exo.menu.show', e => {
            $submenu.off(Drupal.Exo.animationEvent + '.exo.menu.show');
            $submenu.removeClass('exo-animate-' + this.get('transitionIn'));
          });
        }
      }
    }

    protected hide($item:JQuery, animate?:boolean) {
      const $submenu = $item.find('> .exo-menu-level');
      animate = animate !== false;
      if ($submenu.length) {
        if (animate && this.get('transitionOut') !== '' && Drupal.Exo.animationEvent !== undefined) {
          $submenu.off(Drupal.Exo.animationEvent + '.exo.menu.show');
          $submenu.removeClass('exo-animate-' + this.get('transitionIn'))
          $submenu.addClass('exo-animate-' + this.get('transitionOut'));
          $submenu.one(Drupal.Exo.animationEvent + '.exo.menu.hide', e => {
            $item.removeClass('expand');
            $submenu.off(Drupal.Exo.animationEvent + '.exo.menu.hide');
            $submenu.removeClass('exo-animate-' + this.get('transitionOut'));
            if (this.get('expandable')) {
              $submenu.find('.expand').removeClass('expand');
            }
          });
        }
        else {
          $item.removeClass('expand');
        }
        $item.find(this.linkSelector + ':first').attr('aria-expanded', 'false');
      }
    }
  }

  Drupal.ExoMenuStyles['dropdown_horizontal'] = ExoMenuStyleDropdownHorizontal;

})(jQuery);
