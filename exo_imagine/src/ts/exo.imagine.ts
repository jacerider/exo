/**
 * @file
 * Global eXo Image javascript.
 */

(function ($, Drupal, drupalSettings) {

  class ExoImagine {

    protected settings = {
      webp: 1,
      animate: 1,
      blur: 1,
      visible: 1
    };
    public $element:JQuery;
    protected $image:JQuery;
    protected $imageSources:JQuery;
    protected $previewPicture:JQuery;

    constructor(element, globalSettings:any) {
      this.$element = $(element);
      this.$image = this.$element.find('.exo-imagine-image');
      this.$imageSources = this.$element.find('.exo-imagine-image-picture source');
      this.$previewPicture = this.$element.find('.exo-imagine-preview-picture');
      const instanceSettings = JSON.parse(this.$element.attr('data-exo-imagine'));
      if (typeof globalSettings.defaults == 'object') {
        $.extend(this.settings, globalSettings.defaults);
      }
      if (typeof instanceSettings == 'object') {
        $.extend(this.settings, instanceSettings);
      }

      if (this.settings.visible) {
        Drupal.Exo.trackElementPosition(this.$element.get(0), $element => {
          Drupal.Exo.untrackElementPosition($element[0]);
          this.render();
        });
      }
      else {
        this.render();
      }
      this.$element.data('exo.imagine.init');
    }

    public render() {
      return new Promise<void>((resolve, reject) => {
        if (!this.$element.data('exo.imagine.loaded')) {
          // Watch for load.
          this.$image.one('load', e => {
            this.$element.addClass('exo-imagine-loaded');
            if (this.settings.animate) {
              this.$previewPicture.one(Drupal.Exo.transitionEvent, e => {
                this.$previewPicture.remove();
              });
              this.$element.addClass('exo-imagine-animate');
            }
            else {
              this.$previewPicture.remove();
            }
          });

          // Swap in srcset.
          this.$imageSources.each((index, element) => {
            const $source = $(element);
            $source.attr('srcset', $source.data('srcset')).removeAttr('data-srcset');
          });

          this.$element.data('exo.imagine.loaded', true);
          resolve();
        }
      });
    }
  }

  Drupal.behaviors.exoImagine = {
    supportsWebP: null,

    attach: function(context) {
      if (typeof drupalSettings.exoImagine !== 'undefined') {
        $('.exo-imagine', context).once('exo.imagine').each((index, element) => {
          new ExoImagine(element, drupalSettings.exoImagine);
        });
      }
    },

    // This will force-render any exo imagine images. This can be useful for
    // instances like slick which clones the elements before they can be
    // rendered.
    render: function ($wrapper?:JQuery) {
      return new Promise<void>((resolve, reject) => {
        let promises = [];
        $('.exo-imagine', $wrapper).each((index, element) => {
          if (!$(element).data('exo.imagine.loaded')) {
            const instance = new ExoImagine(element, drupalSettings.exoImagine);
            promises.push(instance.render());
          }
        });
        Promise.all(promises).then(values => {
          resolve();
        });
      });
    }
  }

}(jQuery, Drupal, drupalSettings));
