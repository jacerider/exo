(function ($, Drupal, SplitType) {

  class ExoAlchemistEnhancementSplitText {

    protected $wrapper:JQuery;
    protected id:string = '';

    constructor(id:string, $wrapper:JQuery) {
      this.$wrapper = $wrapper;
      this.id = id;

      const doSplitText = () => {
        const type = this.$wrapper.data('ee--split-text-type') || 'lines';
        const delayLines = this.$wrapper.data('ee--split-text-delay-lines');
        const textSplit = new SplitType($wrapper[0], {
          types: type,
          lineClass: 'split-text-line',
          wordClass: 'split-text-word',
          charClass: 'split-text-char',
        });
        if (delayLines) {
          var c = 0;
          textSplit.lines.forEach(element => {
            element.style.setProperty("transition-delay", c * parseInt(delayLines) + 'ms');
            c++;
          });
        }
        setTimeout(() => {
          this.$wrapper.addClass('loaded');
        }, 100);
      };

      let resizeTimer = null;

      document.fonts.onloadingdone = () => {
        console.log('hit');
      };

      document.fonts.ready.then(() => {
        const doInit = () => {
          const $original = this.$wrapper.clone();
          doSplitText();
          Drupal.Exo.addOnResize('exo.alchemist.enhancement.splitText.' + this.id, e => {
            clearTimeout(resizeTimer);
            this.$wrapper.removeClass('loaded').html($original.html());
            resizeTimer = setTimeout(() => {
              doSplitText();
            }, 500);
          });
        };

        this.$wrapper.imagesLoaded(doInit);
      });
    }

    public unload() {
      Drupal.Exo.removeOnResize('exo.alchemist.enhancement.splitText.' + this.id);
    }

  }

  /**
   * eXo Alchemist enhancement behavior.
   */
  Drupal.behaviors.exoAlchemistEnhancementSplitText = {
    count: 0,
    instances: {},
    attach: function(context) {
      const self = this;
      $('.ee--split-text-wrapper', context).once('exo.alchemist.enhancement').each(function () {
        const $wrapper = $(this);
        const id = $wrapper.data('ee--split-text-id');
        $wrapper.data('ee--split-text-count', self.count);
        self.instances[id + self.count] = new ExoAlchemistEnhancementSplitText(id, $wrapper);
        self.count++;
      });
    },
    detach: function detach(context, settings, trigger) {
      if (trigger === 'unload') {
        const self = this;
        $('.ee--split-text-wrapper', context).each(function () {
          const $wrapper = $(this);
          const id = $wrapper.data('ee--split-text-id') + $wrapper.data('ee--split-text-count');
          if (typeof self.instances[id] !== 'undefined') {
            self.instances[id].unload();
            delete self.instances[id];
          }
        });
      }
    }
  }

})(jQuery, Drupal, SplitType);
