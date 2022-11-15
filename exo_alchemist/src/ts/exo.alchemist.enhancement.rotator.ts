(function ($, Drupal) {

  class ExoAlchemistEnhancementRotator {

    protected $wrapper:JQuery;
    protected $itemsWrapper:JQuery;
    protected $items:JQuery;
    protected $next:JQuery;
    protected $prev:JQuery;
    protected $nav:JQuery;
    protected $navItems:JQuery;
    protected $current:JQuery;
    protected id:string = '';
    protected speed:number = 5000;
    protected timeout:number;
    protected lock:boolean = false;

    constructor(id:string, $wrapper:JQuery) {
      this.$wrapper = $wrapper;
      this.id = id;
      this.$items = $wrapper.find('.ee--rotator-item');
      if (this.$items.length > 1) {
        this.$itemsWrapper = $wrapper.find('.ee--rotator-items');
        this.$nav = this.$wrapper.find('.ee--rotator-nav');
        this.$prev = this.$wrapper.find('.ee--rotator-prev');
        this.$next = this.$wrapper.find('.ee--rotator-next');
        this.$navItems = this.$nav.find('.ee--rotator-nav-item');
        this.$current = this.$items.first();
        this.$itemsWrapper.css({
          position: 'relative',
        });
        this.$items.each((index, element) => {
          $(element).data('ee--rotator-index', index).trigger('exoAlchemistRotatorHide');
        }).hide();
        this.$current.show();
        setTimeout(() => {
          this.$current.trigger('exoAlchemistRotatorShow');
        });
        this.setTimeout();

        this.$wrapper.on('keydown', e => {
          switch (e.which) {
            case 39: // right
              e.preventDefault();
              e.stopPropagation();
              this.next();
              break;
            case 37: // right
              e.preventDefault();
              e.stopPropagation();
              this.prev();
              break;
          }
        }).on('swiperight', e => {
          e.preventDefault();
          e.stopPropagation();
          this.next();
        }).on('swipeleft', e => {
          e.preventDefault();
          e.stopPropagation();
          this.prev();
        });
        this.$prev.on('click', e => {
          this.prev();
        }).on('keydown', e => {
          switch (e.which) {
            case 13: // enter
            case 32: // space
              e.preventDefault();
              e.stopPropagation();
              this.prev();
              break;
          }
        });
        this.$next.on('click', e => {
          this.next();
        }).on('keydown', e => {
          switch (e.which) {
            case 13: // enter
            case 32: // space
              e.preventDefault();
              e.stopPropagation();
              this.next();
              break;
          }
        });
        // Navigation.
        if (this.$nav.length) {
          if (this.$navItems.length > 1) {
            this.$navItems.first().addClass('active');
            this.$navItems.each((index, element) => {
              $(element).data('ee--rotator-index', index);
            }).on('click', e => {
              this.goto($(e.target).data('ee--rotator-index'));
            }).on('keydown', e => {
              switch (e.which) {
                case 13: // enter
                case 32: // space
                  e.preventDefault();
                  e.stopPropagation();
                  this.goto($(e.target).data('ee--rotator-index'));
                  break;
              }
            });
          }
        }

        if (this.isLayoutBuilder()) {
          this.buildForLayoutBuilder();
        }
        else {
          if (this.$wrapper.data('ee--rotator-pauseonhover')) {
            this.$wrapper.on('mouseover', e => {
              this.pause();
            }).on('mouseleave', e => {
              this.play();
            });
          }
        }
      }
    }

    protected setTimeout() {
      clearTimeout(this.timeout);
      this.timeout = setTimeout(() => {
        this.cycle();
      }, this.$wrapper.data('ee--rotator-speed') || this.speed);
    }

    protected buildForLayoutBuilder():void {
      if (this.$prev.length) {
        this.$prev.on('click', e => {
          this.pause();
        });
      }
      if (this.$next.length) {
        this.$next.on('click', e => {
          this.pause();
        });
      }
      if (this.$navItems.length) {
        this.$navItems.on('click', e => {
          this.pause();
        });
      }
      $(document).on('exoComponentOps.exo.alchemist.enhancement.rotator.' + this.id, (e, element) => {
        if (Drupal.ExoAlchemistAdmin.getActiveComponent().find(this.$wrapper).length) {
          let $element = $(element);

          $element.find('.exo-field-op-rotator-prev').off('click').on('click', e => {
            e.preventDefault();
            this.prev();
          });
          $element.find('.exo-field-op-rotator-next').off('click').on('click', e => {
            e.preventDefault();
            this.next();
          });
        }
      });
      $(document).on('exoComponentActive.exo.alchemist.enhancement.rotator.' + this.id, (e, element) => {
        if ($(element).find(this.$wrapper).length) {
          if (this.$prev.length || this.$next.length || this.$navItems.length) {
            this.pause();
          }
        }
      });
      $(document).on('exoComponentInactive.exo.alchemist.enhancement.rotator.' + this.id, (e, element) => {
        if ($(element).find(this.$wrapper).length) {
          this.play();
        }
      });
      $(document).on('exoComponentFieldEditActive.exo.alchemist.enhancement.rotator.' + this.id, (e, element) => {
        let $element = $(element);
        if (this.$wrapper.find($element).length) {
          this.pause();
          let $slide = this.$items.filter($element);
          if (!$slide.length) {
            $slide = $element.closest('.ee--rotator-item');
          }
          else if (!$slide.length) {
            $slide = $element.find('.ee--rotator-item');
          }
          if ($slide.length && $slide.data('ee--rotator-index') !== this.$current.data('ee--rotator-index')) {
            this.cycle($slide, 0);
            Drupal.ExoAlchemistAdmin.sizeFieldOverlay($element);
            Drupal.ExoAlchemistAdmin.sizeTarget($element);
          }
        }
      });
      $(document).on('exoComponentFieldEditInactive.exo.alchemist.enhancement.rotator.' + this.id, (e, element) => {
        const $element = $(element);
        if (this.$wrapper.find($element).length) {
          if (!this.$prev.length && !this.$next.length && !this.$navItems.length) {
            this.play();
          }
        }
      });
    }

    public pause():void {
      clearTimeout(this.timeout);
    }

    public play():void {
      this.setTimeout();
    }

    public prev():void {
      this.cycle(this.getPrev());
    }

    public next():void {
      this.cycle();
    }

    public goto(index:number):void {
      this.$items.each((i, element) => {
        if (i === index) {
          this.cycle($(element));
        }
      });
    }

    public getNext():JQuery {
      let $next = this.$current.next();
      if ($next.length === 0 || !$next.hasClass('ee--rotator-item')) {
        $next = this.$items.first();
      }
      return $next;
    }

    public getPrev():JQuery {
      let $prev = this.$current.prev();
      if ($prev.length === 0 || !$prev.hasClass('ee--rotator-item')) {
        $prev = this.$items.last();
      }
      return $prev;
    }

    public cycle($to?:JQuery, speed?:number):void {
      this.setTimeout();
      const currentIndex = this.$current.data('ee--rotator-index')
      speed = typeof speed !== 'undefined' ? speed : 1000;
      const $from = this.$current;
      $from.css({
        zIndex: 2,
        position: 'relative',
      });
      $to = $to || this.getNext();
      const index = $to.data('ee--rotator-index');
      if (this.lock === true || index === currentIndex) {
        return;
      }
      this.lock = true;
      $to.css({
        zIndex: 1,
        position: 'absolute',
        top: 0,
        left: 0,
        right: 0,
      });
      this.$itemsWrapper.height($from.outerHeight());
      if (this.$navItems.length) {
        this.$navItems.removeClass('active');
        $(this.$navItems.get(index)).addClass('active');
      }
      setTimeout(() => {
        $to.show().trigger('exoAlchemistRotatorShow');
        Drupal.Exo.checkElementPosition();
        this.$itemsWrapper.height($to.outerHeight());
        $from.css('z-index', 2);
        setTimeout(() => {
          $from.fadeOut(speed, 'swing', () => {
            $from.trigger('exoAlchemistRotatorHide');
            this.$itemsWrapper.height('');
            $to.css({
              position: 'relative',
            });
            this.lock = false;
          });
        });
        this.$current = $to;
      });
    }

    public unload() {
      $(document).off('exoComponentOps.exo.alchemist.enhancement.rotator.' + this.id);
      $(document).off('exoComponentActive.exo.alchemist.enhancement.rotator.' + this.id);
      $(document).off('exoComponentInactive.exo.alchemist.enhancement.rotator.' + this.id);
      $(document).off('exoComponentFieldEditActive.exo.alchemist.enhancement.rotator.' + this.id);
      $(document).off('exoComponentFieldEditInactive.exo.alchemist.enhancement.rotator.' + this.id);
    }

    protected isLayoutBuilder() {
      return Drupal.ExoAlchemistAdmin && Drupal.ExoAlchemistAdmin.isLayoutBuilder();
    }

  }

  /**
   * eXo Alchemist enhancement behavior.
   */
  Drupal.behaviors.exoAlchemistEnhancementRotator = {
    count: 0,
    instances: {},
    attach: function(context) {
      const self = this;
      $('.ee--rotator-wrapper').once('exo.alchemist.enhancement').each(function () {
        const $wrapper = $(this);
        const id = $wrapper.data('ee--rotator-id');
        self.instances[id + self.count] = new ExoAlchemistEnhancementRotator(id, $wrapper);
        $wrapper.data('ee--rotator-count', self.count);
        self.count++;
      });
    },
    detach: function detach(context, settings, trigger) {
      if (trigger === 'unload') {
        var self = this;
        $('.ee--rotator-wrapper', context).each(function () {
          const $wrapper = $(this);
          const id = $wrapper.data('ee--rotator-id') + $wrapper.data('ee--rotator-count');
          if (typeof self.instances[id] !== 'undefined') {
            self.instances[id].unload();
            delete self.instances[id];
          }
        });
      }
    }
  }

})(jQuery, Drupal);
