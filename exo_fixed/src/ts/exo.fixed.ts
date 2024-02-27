(function ($, _, Drupal, drupalSettings, displace) {

  class ExoFixed extends ExoData {
    protected $wrapper:JQuery;
    protected $element:JQuery;
    protected offset:{top: number; left: number; right?: number;};
    protected floatOffset:number;
    protected floatStart:number = 0;
    protected floatEnd:number = 0;
    protected themeStart:number = 0;
    protected themeEnd:number = 0;
    protected width:number;
    protected height:number;
    protected fixed:boolean = false;
    protected themed:boolean = false;
    protected lastScrollTop:number = 0;
    protected lastDirection:string;
    protected type:string;
    protected isLocked:boolean = false;
    protected windowWidth:number;

    constructor(id:string, $wrapper:JQuery) {
      super(id);
      this.$wrapper = $wrapper;
    }

    public build(data):Promise<ExoSettingsGroupInterface> {
      return new Promise((resolve, reject) => {
        super.build(data).then(data => {
          if (data !== null) {
            this.type = this.get('type');
            if (this.type !== 'sticky') {
              this.type = Drupal.Exo.isMobile() ? 'scroll' : this.type;
            }
            this.lastDirection = this.type === 'scroll' ? 'up' : 'down';
            this.$element = this.$wrapper.find('.exo-fixed-element');
            this.bind();

            // When we start display from mid page we do not want any animations
            // happening.
            this.$wrapper.addClass('exo-fixed-no-animations');
            this.resize();
            this.onScroll();
            setTimeout(() => {
              this.$wrapper.removeClass('exo-fixed-no-animations');
            }, 10);
          }
          resolve(data);
        }, reject);
      });
    }

    protected bind() {
      // Call on scroll.
      const onScroll = _.throttle(() => {
        this.onScroll();
      }, 10);
      Drupal.Exo.$window.on('scroll.exo.fixed', e => {
        onScroll();
      });

      // Let Drupal handling resizing event.
      Drupal.Exo.addOnResize('exo.fixed.' + this.getId(), () => {
        if (this.windowWidth !== Drupal.Exo.$window.width()) {
          this.$wrapper.addClass('exo-fixed-no-animations');
          this.resize();
          this.onScroll();
          setTimeout(() => {
            this.$wrapper.removeClass('exo-fixed-no-animations');
          }, 10);
          this.windowWidth = Drupal.Exo.$window.width();
        }
      });
    }

    protected resize() {
      this.reset();
      this.calcSize();
      this.setSize();
    }

    protected reset() {
      this.fixed = false;
      this.themed = false;
      this.$wrapper.removeAttr('style');
      this.$element.removeAttr('style');
      if (this.type === 'sticky') {
        this.$element.parent().removeAttr('style');
      }
      this.$element.removeClass('exo-fixed-float exo-fixed-hide exo-fixed-theme');
    }

    protected calcSize() {
      this.offset = this.$element.offset();
      this.offset.right = (Drupal.Exo.$window.width() - (this.offset.left + this.$element.outerWidth()))
      this.floatOffset = 0;
      this.width = Math.min(this.$element.outerWidth(), Drupal.Exo.$window.width());
      this.height = this.type === 'sticky' ? this.$element.parent().outerHeight() : this.$element.outerHeight();
      this.floatStart = Math.round(this.offset.top - this.floatOffset - displace.offsets.top);
      this.floatStart = this.floatStart >= 0 ? this.floatStart : 0;
      // Settings to -1 means it will continue to be floated. This will only
      // apply to items that are flush to the top.
      this.floatEnd = this.floatStart === 0 ? -1 : this.floatStart;
      this.themeStart = Math.round(this.floatStart + this.height);
      this.themeEnd = Math.round(this.floatEnd + this.height);
      if (this.type === 'scroll') {
        this.floatEnd = this.floatStart <= 0 ? 1 : this.floatStart;
        this.themeEnd = Math.round(this.floatEnd + 1);
        this.floatStart = Math.round(this.floatEnd + this.height);
        this.themeStart = this.floatStart;
        this.themeEnd = Math.round(this.floatStart + 1);
      }
    }

    protected setSize() {
      this.$wrapper.css({width: this.width, height: this.height});
    }

    protected onScroll() {
      var scrollTop = Math.max(Drupal.Exo.$window.scrollTop(), 0);
      var direction = scrollTop > this.lastScrollTop ? 'down' : 'up';
      if (Math.abs(this.lastScrollTop - scrollTop) > 50) {
        this.lastDirection = direction;
        this.lastScrollTop = scrollTop;
      }

      if (this.isLocked) {
        return;
      }

      let floatOffset = this.floatOffset;
      $('.exo-fixed').each((index, element) => {
        if (element === this.$wrapper.get(0)) {
          return;
        }
        if (!$(element).find('.exo-fixed-hide').length && $(element).offset().top < this.offset.top) {
          floatOffset += $(element).height();
        }
      });

      if (this.themed === false && direction === 'down' && scrollTop >= this.themeStart) {
        this.themed = true;
        this.$element.one(Drupal.Exo.transitionEvent, () => {
          this.updateStyleProps(floatOffset);
        });
        this.$element.addClass('exo-fixed-theme');
        this.updateStyleProps(floatOffset);
      }
      else if (this.themed === true && direction === 'up' && scrollTop <= this.themeEnd) {
        this.themed = false;
        this.$element.one(Drupal.Exo.transitionEvent, () => {
          this.updateStyleProps(floatOffset);
        });
        this.$element.removeClass('exo-fixed-theme');
        this.updateStyleProps(floatOffset);
      }

      if (this.type === 'scroll') {
        if (this.lastDirection === 'down') {
          this.$element.addClass('exo-fixed-hide');
        }
        else {
          this.$element.removeClass('exo-fixed-no-animations exo-fixed-hide');
        }
      }

      if (this.lastDirection === 'down' && scrollTop > (this.floatStart - floatOffset)) {
        if (this.fixed === false) {
          this.doFloat(floatOffset);
        }
      }
      else if (this.lastDirection === 'up' && this.fixed === true && scrollTop <= (this.floatEnd - floatOffset)) {
        this.unFloat();
      }
    }

    protected updateStyleProps(floatOffset:number) {
      let height = this.$element.outerHeight();
      var transformMatrix = this.$element.css("-webkit-transform") ||
        this.$element.css("-moz-transform") ||
        this.$element.css("-ms-transform") ||
        this.$element.css("-o-transform") ||
        this.$element.css("transform");
      var matrix = transformMatrix.replace(/[^0-9\-.,]/g, '').split(',');
      var x = matrix[12] || matrix[4];
      var y = matrix[13] || matrix[5];
      Drupal.Exo.addStyleProp('fixed-' + this.getId() + '-top', (height + floatOffset) + 'px');
      Drupal.Exo.addStyleProp('fixed-' + this.getId() + '-x', x + 'px');
      Drupal.Exo.addStyleProp('fixed-' + this.getId() + '-y', y + 'px');
      Drupal.Exo.addStyleProp('fixed-' + this.getId() + '-left', this.offset.left + 'px');
      Drupal.Exo.addStyleProp('fixed-' + this.getId() + '-right', this.offset.right + 'px');
      Drupal.Exo.updateStyle();
    }

    protected resetScroll() {
      this.lastScrollTop = Drupal.Exo.$window.scrollTop();
      this.lastDirection = 'down';
    }

    protected doFloat(floatOffset:number) {
      this.fixed = true;
      if (this.type === 'scroll') {
        this.$element.addClass('exo-fixed-no-animations exo-fixed-hide');
      }
      if (this.type === 'sticky') {
        this.$element.css({
          position: 'sticky',
          top: floatOffset + displace.offsets.top,
        });
      }
      else {
        this.$element.css({
          position: 'fixed',
          marginLeft: (this.offset.left - displace.offsets.left),
          marginRight: (this.offset.right - displace.offsets.right),
          maxWidth: this.width,
          top: floatOffset + displace.offsets.top,
          left: displace.offsets.left,
          right: displace.offsets.right
        });
      }

      this.$element.one(Drupal.Exo.transitionEvent, () => {
        this.updateStyleProps(floatOffset);
      });
      this.$element.addClass('exo-fixed-float');
      setTimeout(() => {
        this.updateStyleProps(floatOffset);
      });

    }

    protected unFloat() {
      this.reset();
      this.setSize();
      this.$element.removeClass('exo-fixed-float');
      Drupal.Exo.removeStyleProp('fixed-' + this.getId() + '-top');
      Drupal.Exo.removeStyleProp('fixed-' + this.getId() + '-left');
      Drupal.Exo.removeStyleProp('fixed-' + this.getId() + '-right');
    }

    protected lock() {
      this.isLocked = true;
    }

    protected unlock() {
      this.resetScroll();
      this.isLocked = false;
    }
  }

  /**
   * Fixed build behavior.
   */
  Drupal.behaviors.exoFixed = {
    ready: false,
    instances: [],

    attach: function(context) {
      if (typeof drupalSettings.exoFixed !== 'undefined' && typeof drupalSettings.exoFixed.elements !== 'undefined') {
        if (this.ready === false) {
          Drupal.Exo.event('ready').on('exo.fixed', () => {
            this.ready = true;
            this.build();
            $(document).on('drupalViewportOffsetChange.exo.fixed', e => {
              this.resize();
              $(document).off('drupalViewportOffsetChange.exo.fixed');
            });
          });
        }
        else {
          this.build();
        }
      }
    },

    build: function () {
      const data = [];
      const sortByWeight = function(a, b) {
        var top1 = a.top;
        var top2 = b.top;
        return ((top1 < top2) ? -1 : ((top1 > top2) ? 1 : 0));
      }
      for (const elementId in drupalSettings.exoFixed.elements) {
        if (drupalSettings.exoFixed.elements.hasOwnProperty(elementId)) {
          const settings = drupalSettings.exoFixed.elements[elementId];
          if (settings.hasOwnProperty('selector')) {
            let $element = $(settings.selector).first().once('exo.fixed');
            if ($element.length) {
              data.push({
                id: elementId,
                $element: $element,
                settings: settings,
                top: $element.offset().top,
              });
            }
          }
        }
      }
      if (data.length) {
        data.sort(sortByWeight);
        data.forEach((element) => {
          element.$element.imagesLoaded(() => {
            const fixed = new ExoFixed(element.id, element.$element);
            this.instances.push(fixed);
            fixed.build(element.settings);
          });
        });
      }
    },

    lock: function() {
      for (let index = 0; index < this.instances.length; index++) {
        const fixed = this.instances[index];
        fixed.lock();
      }
    },

    unlock: function() {
      for (let index = 0; index < this.instances.length; index++) {
        const fixed = this.instances[index];
        fixed.unlock();
      }
    },

    resize: function() {
      for (let index = 0; index < this.instances.length; index++) {
        const fixed = this.instances[index];
        fixed.resize();
        fixed.onScroll();
      }
    }
  }

})(jQuery, _, Drupal, drupalSettings, Drupal.displace);
