(function ($, Drupal, Swiper) {

  class ExoAlchemistEnhancementSlider {

    protected $wrapper:JQuery;
    protected id:string = '';
    protected swiper:any;

    constructor(id:string, $wrapper:JQuery) {
      this.$wrapper = $wrapper;
      this.id = id;
      const $pagination = $wrapper.find('.swiper-pagination');
      const $next = $wrapper.find('.swiper-button-next');
      const $prev = $wrapper.find('.swiper-button-prev');
      const $scrollbar = $wrapper.find('.swiper-scrollbar');
      const defaultSettings = {
        pagination: {},
        navigation: {},
        scrollbar: {},
      };
      if ($pagination.length) {
        defaultSettings['pagination']['el'] = $pagination.get(0);
      }
      if ($next.length) {
        defaultSettings['navigation']['nextEl'] = $next.get(0);
      }
      if ($prev.length) {
        defaultSettings['navigation']['prevEl'] = $prev.get(0);
      }
      if ($scrollbar.length) {
        defaultSettings['scrollbar']['el'] = $scrollbar.get(0);
      }
      const settings = $.extend(true, {}, defaultSettings, this.$wrapper.data('ee--slider-settings') || {});
      this.swiper = new Swiper(this.$wrapper.get(0), settings);
      if (typeof Drupal.behaviors.exoImagine != 'undefined') {
        this.swiper.on('slideChange', () => {
          Drupal.behaviors.exoImagine.render(this.$wrapper);
        });
      }
      if (this.isLayoutBuilder()) {
        this.buildForLayoutBuilder();
      }
    }

    protected buildForLayoutBuilder():void {
      $(document).on('exoComponentOps.exo.alchemist.enhancement.slider.' + this.id, (e, element) => {
        if (Drupal.ExoAlchemistAdmin.getActiveComponent().find(this.$wrapper).length) {
          let $element = $(element);
          $element.find('.exo-field-op-rotator-prev').off('click').on('click', e => {
            e.preventDefault();
            this.swiper.slidePrev();
          });
          $element.find('.exo-field-op-rotator-next').off('click').on('click', e => {
            e.preventDefault();
            this.swiper.slideNext();
          });
        }
      });

      $(document).on('exoComponentFieldEditActive.exo.alchemist.enhancement.slider.' + this.id, (e, element) => {
        let $element = $(element);
        if (this.$wrapper.find($element).length) {
          this.swiper.slideTo($element.index());
          this.swiper.once('slideChangeTransitionEnd', function () {
            Drupal.ExoAlchemistAdmin.sizeFieldOverlay($element);
            Drupal.ExoAlchemistAdmin.sizeTarget($element);
          });
        }
      });
    }

    public unload() {
      $(document).off('exoComponentOps.exo.alchemist.enhancement.slider.' + this.id);
      $(document).off('exoComponentFieldEditActive.exo.alchemist.enhancement.slider.' + this.id);
      this.swiper.destroy();
    }

    protected isLayoutBuilder() {
      return Drupal.ExoAlchemistAdmin && Drupal.ExoAlchemistAdmin.isLayoutBuilder();
    }

  }

  /**
   * eXo Alchemist enhancement behavior.
   */
  Drupal.behaviors.exoAlchemistEnhancementSlider = {
    count: 0,
    instances: {},
    attach: function(context) {
      var self = this;
      $('.ee--slider-wrapper', context).once('exo.alchemist.enhancement').each(function () {
        const $wrapper = $(this);
        const id = $wrapper.data('ee--slider-id');
        $wrapper.data('ee--slider-count', self.count);
        self.instances[id + self.count] = new ExoAlchemistEnhancementSlider(id, $wrapper);
        self.count++;
      });
    },
    detach: function detach(context, settings, trigger) {
      if (trigger === 'unload') {
        var self = this;
        $('.ee--slider-wrapper', context).each(function () {
          const $wrapper = $(this);
          const id = $wrapper.data('ee--slider-id') + $wrapper.data('ee--slider-count');
          if (typeof self.instances[id] !== 'undefined') {
            self.instances[id].unload();
            delete self.instances[id];
          }
        });
      }
    }
  }

})(jQuery, Drupal, Swiper);
