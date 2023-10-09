(function ($, Drupal, drupalSettings) {

  class ExoAlchemistEnhancementAccordion {

    protected $wrapper:JQuery;
    protected $items:JQuery;
    protected $triggers:JQuery;
    protected $contents:JQuery;
    protected $current:JQuery;
    protected id:string = '';
    protected idSelector:string = '';
    protected speed:number = 5000;
    protected interval:number;
    protected history:boolean = false;
    protected require:boolean = false;
    protected style:string;

    constructor(id:string, $wrapper:JQuery) {
      this.$wrapper = $wrapper;
      this.id = id;
      this.idSelector = 'data-ee--accordion-id="' + this.id + '"';
      this.$items = $wrapper.find('.ee--accordion-item[' + this.idSelector + ']');
      this.$triggers = $wrapper.find('.ee--accordion-trigger[' + this.idSelector + ']');
      this.$contents = $wrapper.find('.ee--accordion-content[' + this.idSelector + ']');
      this.history = typeof $wrapper.data('ee--accordion-history') !== 'undefined';
      this.require = typeof $wrapper.data('ee--accordion-require') !== 'undefined';
      this.style = $wrapper.data('ee--accordion-style') || 'vertical';
      const collapse = typeof $wrapper.data('ee--accordion-collapse') !== 'undefined';
      if (this.style !== 'none') {
        this.$contents.hide();
      }
      let $show = this.$triggers.first();
      let forceShow = false;

      const hashTrigger = () => {
        const hashTab = Drupal.ExoAlchemistEnhancement.getHashForKey('ee--accordion');
        if (hashTab && typeof hashTab[this.id] !== 'undefined') {
          const $item = this.$triggers.filter('[data-ee--accordion-item-id="' + hashTab[this.id] + '"]');
          if ($item.length) {
            $show = $item.first();
            forceShow = true;
          }
        }
      }

      this.$items.each((index, element) => {
        const $item = $(element);
        const $trigger = $item.find('.ee--accordion-trigger');
        const $content = $item.find('.ee--accordion-content');
        if (!$trigger.data('ee--accordion-item-id')) {
          const triggerId = this.id + '-trigger-' + index;
          const contentId = this.id + '-content-' + index;
          $trigger.attr('id', triggerId).attr('data-ee--accordion-item-id', index).attr('aria-controls', contentId);
          $content.attr('id', contentId).attr('aria-labelledby', triggerId);
        }
      });

      Drupal.Exo.$window.on('popstate.exo.alchemist.enhancement.tabs.' + this.id, e => {
        const hashTab = Drupal.ExoAlchemistEnhancement.getHashForKey('ee--accordion');
        if (hashTab && typeof hashTab[this.id] !== 'undefined') {
          const $item = this.$triggers.filter('[data-ee--accordion-item-id="' + hashTab[this.id] + '"]');
          if ($item.length) {
            this.show($item.first(), true, true, false);
          }
        }
        else {
          this.show(this.$triggers.first(), true, true, false);
        }
      });

      if (this.isLayoutBuilder()) {
        Drupal.ExoAlchemistAdmin.lockNestedFields(this.$items);
        Drupal.Exo.$document.on('exoComponentFieldEditActive.exo.alchemist.enhancement.accordion.' + this.id, (e, element) => {
          const $element = $(element);
          if ($element.hasClass('ee--accordion-item') && this.$wrapper.find($element).length) {
            this.show($element, false, false, this.history);
            Drupal.ExoAlchemistAdmin.sizeFieldOverlay($element);
            Drupal.ExoAlchemistAdmin.sizeTarget($element);
          }
        });
      }
      else {
        hashTrigger();
      }
      this.$triggers.on('click.exo.alchemist.enhancement.accordion', e => {
        e.preventDefault();
        this.show($(e.currentTarget), true, this.keepOpen(), this.history);
      }).on('keydown.exo.alchemist.enhancement.accordion', e => {
        let $goto;
        switch (e.which) {
          case 13: // enter
          case 32: // space
            e.preventDefault();
            e.stopPropagation();
            this.show($(e.currentTarget), true, this.keepOpen(), this.history);
            break;
          case 40: // down
            e.preventDefault();
            e.stopPropagation();
            $goto = $(e.currentTarget).closest('.ee--accordion-item[' + this.idSelector + ']').next().find('.ee--accordion-trigger[' + this.idSelector + ']');
            if ($goto.length) {
              this.show($goto, true, this.keepOpen(), this.history);
              $goto.focus();
            }
            break;
          case 38: // up
            e.preventDefault();
            e.stopPropagation();
            $goto = $(e.currentTarget).closest('.ee--accordion-item[' + this.idSelector + ']').prev().find('.ee--accordion-trigger[' + this.idSelector + ']');
            if ($goto.length) {
              this.show($goto, true, this.keepOpen(), this.history);
              $goto.focus();
            }
            break;
        }
      });
      if (collapse === false || forceShow) {
        Drupal.Exo.$window.on('ee--tab.open.' + this.id, (e, params) => {
          if (params.content.find(this.$wrapper).length) {
            this.show($show, true, true, false);
          }
        });
        this.show($show, false, true, false);
      }
    }

    protected keepOpen() {
      return this.require || this.isLayoutBuilder();
    }

    public show($trigger:JQuery, animate?:boolean, keepOpen?:boolean, doHash?:boolean):void {
      animate = typeof animate !== 'undefined' ? animate : true;
      keepOpen = typeof keepOpen !== 'undefined' ? keepOpen : false;
      doHash = typeof doHash !== 'undefined' ? doHash : true;
      const $item = $trigger.closest('.ee--accordion-item[' + this.idSelector + ']');
      const $contents = $item.find('.ee--accordion-content[' + this.idSelector + ']');
      const itemId = $trigger.data('ee--accordion-item-id');
      if ($contents.length) {
        const current = $item.hasClass('show');
        const $shown = this.$items.filter('.show');
        const $shownContent = $shown.find('.ee--accordion-content[' + this.idSelector + ']');
        if (this.isLayoutBuilder()) {
          if (current) {
            return;
          }
          Drupal.ExoAlchemistAdmin.lockNestedFields($shown);
        }
        if ((current && !keepOpen) || !current) {
          $shown.removeClass('show');
          $trigger.attr('aria-expanded', 'false');
          if (doHash && typeof itemId !== 'undefined') {
            Drupal.ExoAlchemistEnhancement.removeHashForKey('ee--accordion', itemId, this.id);
          }
          if (animate && this.style !== 'none') {
            if (this.style === 'horizontal') {
              setTimeout(() => {
                $shownContent.animate({width: 'toggle', opacity: 'toggle'}, 350);
              });
            }
            else {
              $shownContent.slideToggle(350, 'swing');
            }
          }
          else {
            $shown.removeClass('shown');
          }
        }
        if ((!current || keepOpen) && doHash && typeof itemId !== 'undefined') {
          Drupal.ExoAlchemistEnhancement.setHashForKey('ee--accordion', itemId, this.id);
        }
        if (!current) {
          $item.addClass('show');
          this.$wrapper.attr('data-ee--accordion-show', itemId);
          $trigger.attr('aria-expanded', 'true');
          if (animate && this.style !== 'none') {
            const callback = () => {
              // Notify exo about a change in positions.
              Drupal.Exo.checkElementPosition();
              if (this.isLayoutBuilder()) {
                Drupal.ExoAlchemistAdmin.unlockNestedFields($item);
              }
            };
            if (this.style === 'horizontal') {
              $contents.animate({width: 'toggle', opacity: 'toggle'}, 350, 'swing', callback);
            }
            else {
              $contents.slideToggle(350, 'swing', callback);
            }
          }
          else {
            setTimeout(() => {
              $item.addClass('shown');
            }, 10);
            // Notify exo about a change in positions.
            Drupal.Exo.checkElementPosition();
            if (this.isLayoutBuilder()) {
              Drupal.ExoAlchemistAdmin.unlockNestedFields($item);
            }
          }
        }
      }
    }

    public unload() {
      Drupal.Exo.$document.off('exoComponentFieldEditActive.exo.alchemist.enhancement.accordion.' + this.id);
      Drupal.Exo.$window.off('popstate.exo.alchemist.enhancement.accordion.' + this.id);
      Drupal.Exo.$window.off('ee--tab.open.' + this.id);
    }

    protected isLayoutBuilder() {
      return Drupal.ExoAlchemistAdmin && Drupal.ExoAlchemistAdmin.isLayoutBuilder();
    }

  }

  /**
   * eXo Alchemist enhancement behavior.
   */
  Drupal.behaviors.exoAlchemistEnhancementAccordion = {
    count: 0,
    instances: {},
    attach: function(context) {
      var self = this;
      $('.ee--accordion-wrapper', context).once('exo.alchemist.enhancement').each(function () {
        const $wrapper = $(this);
        const id = $wrapper.data('ee--accordion-id');
        $wrapper.data('ee--accordion-count', self.count);
        self.instances[id + self.count] = new ExoAlchemistEnhancementAccordion(id, $wrapper);
        self.count++;
      });
    },
    detach: function detach(context, settings, trigger) {
      if (trigger === 'unload') {
        var self = this;
        $('.ee--accordion-wrapper', context).each(function () {
          const $wrapper = $(this);
          const id = $wrapper.data('ee--accordion-id') + $wrapper.data('ee--accordion-count');
          if (typeof self.instances[id] !== 'undefined') {
            self.instances[id].unload();
            delete self.instances[id];
          }
        });
      }
    }
  }

})(jQuery, Drupal, drupalSettings);
