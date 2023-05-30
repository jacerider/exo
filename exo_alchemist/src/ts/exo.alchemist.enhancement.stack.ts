(function ($, Drupal, displace) {

  // utility functions
  if (typeof Util === 'undefined') {
    var Util:any = () => {}
  }

  Util.osHasReducedMotion = function () {
    if (!window.matchMedia) {
      return false;
    }
    var matchMediaObj = window.matchMedia('(prefers-reduced-motion: reduce)');
    if (matchMediaObj) {
      return matchMediaObj.matches;
    }
    return false;
  };

  class ExoAlchemistEnhancementStack {

    protected $wrapper:JQuery;
    protected $items:JQuery;
    protected id:string = '';
    protected count:any;

    constructor(id:string, $wrapper:JQuery) {
      this.$wrapper = $wrapper;
      this.$items = $wrapper.find('.ee--stack-item');
      var $items = [];
      this.id = id;

      var StackCards = function (element) {
        this.element = element;
        this.items = this.element.getElementsByClassName('ee--stack-item');
        this.scrollingFn = false;
        this.scrolling = false;
        initStackCardsEffect(this);
        initStackCardsResize(this);
      };

      function initStackCardsEffect(element) { // use Intersection Observer to trigger animation
        setStackCards(element); // store cards CSS properties
        var observer = new IntersectionObserver($wrapperCallback.bind(element), {
          threshold: [0, 1]
        });
        observer.observe(element.element);
      }

      function initStackCardsResize(element) { // detect resize to reset gallery
        element.element.addEventListener('resize-stack-cards', function () {
          setStackCards(element);
          animateStackCards.bind(element);

          // Trigger scroll event.
          window.scrollTo(window.scrollX, window.scrollY - 1);
          window.scrollTo(window.scrollX, window.scrollY + 1);
        });
      }

      function $wrapperCallback(entries) { // Intersection Observer callback
        if (entries[0].isIntersecting) {
          if (this.scrollingFn) {
            return; // listener for scroll event already added
          }
          $wrapperInitEvent(this);
        }
        else {
          if (!this.scrollingFn) {
            return; // listener for scroll event already removed
          }
          window.removeEventListener('scroll', this.scrollingFn);
          this.scrollingFn = false;
        }
      }

      function $wrapperInitEvent(element) {
        element.scrollingFn = $wrapperScrolling.bind(element);
        window.addEventListener('scroll', element.scrollingFn);
      }

      function $wrapperScrolling() {
        if (this.scrolling) {
          return;
        }
        this.scrolling = true;
        window.requestAnimationFrame(animateStackCards.bind(this));
      }

      function setStackCards(element) {
        // store wrapper properties
        element.marginY = getComputedStyle(element.element).getPropertyValue('--stack-cards-gap');
        // element.marginY = '30px';
        getIntegerFromProperty(element); // convert element.marginY to integer (px value)
        element.elementHeight = element.element.offsetHeight;

        // store card properties
        var cardStyle = getComputedStyle(element.items[0]);
        element.cardTop = Math.floor(parseFloat(cardStyle.getPropertyValue('top')));
        element.cardHeight = Math.floor(parseFloat(cardStyle.getPropertyValue('height')));

        // store window property
        element.windowHeight = window.innerHeight;

        // reset margin + translate values
        if (isNaN(element.marginY)) {
          element.element.style.paddingBottom = '0px';
        }
        else {
          element.element.style.paddingBottom = (element.marginY * (element.items.length - 1)) + 'px';
        }

        for (var i = 0; i < element.items.length; i++) {
          var top = displace.offsets.top + element.marginY;
          var $header = $('.exo-fixed-header');
          if ($header.length) {
            top += $header.outerHeight();
          }
          element.items[i].style.top = top + 'px';
          if (isNaN(element.marginY)) {
            element.items[i].style.transform = 'none;';
          }
          else {
            element.items[i].style.transform = 'translateY(' + element.marginY * i + 'px)';
          }
        }
      }

      function getIntegerFromProperty(element) {
        var node = document.createElement('div');
        node.setAttribute('style', 'opacity:0; visbility: hidden;position: absolute; height:' + element.marginY);
        element.element.appendChild(node);
        element.marginY = parseInt(getComputedStyle(node).getPropertyValue('height'));
        element.element.removeChild(node);
      }

      function animateStackCards() {
        if (isNaN(this.marginY)) { // --stack-cards-gap not defined - do not trigger the effect
          this.scrolling = false;
          return;
        }

        var top = this.element.getBoundingClientRect().top;

        if (this.cardTop - top + this.element.windowHeight - this.elementHeight - this.cardHeight + this.marginY + this.marginY * this.items.length > 0) {
          this.scrolling = false;
          return;
        }

        for (var i = 0; i < this.items.length; i++) { // use only scale
          var scrolling = this.cardTop - top - i * (this.cardHeight + this.marginY);
          if (scrolling > 0) {
            var scaling = i === this.items.length - 1 ? 1 : (this.cardHeight - scrolling * 0.05) / this.cardHeight;
            this.items[i].style.transform = 'translateY(' + this.marginY * i + 'px) scale(' + scaling + ')';
            if (this.items.length !== i + 1) {
              this.items[i].classList.add('ee--stack-item-back');
            }
          }
          else {
            this.items[i].style.transform = 'translateY(' + this.marginY * i + 'px)';
            this.items[i].classList.remove('ee--stack-item-back');
          }
        }

        this.scrolling = false;
      }

      // initialize StackCards object
      var intersectionObserverSupported = ('IntersectionObserver' in window && 'IntersectionObserverEntry' in window && 'intersectionRatio' in window.IntersectionObserverEntry.prototype);
      var reducedMotion = Util.osHasReducedMotion();

      if ($wrapper.length > 0 && intersectionObserverSupported && !reducedMotion) {
        for (var i = 0; i < $wrapper.length; i++) {
          (function (i) {
            $items.push(new StackCards($wrapper[i]));
          })(i);
        }

        var resizingId = null;
        var customEvent = new CustomEvent('resize-stack-cards');

        $(document).on('drupalViewportOffsetChange.' + id, () => {
          doneResizing();
        });

        window.addEventListener('resize', function () {
          clearTimeout(resizingId);
          resizingId = setTimeout(doneResizing, 500);
        });

        function doneResizing() {
          for (var i = 0; i < $items.length; i++) {
            (function (i) {
              $items[i].element.dispatchEvent(customEvent);
            })(i);
          }
        }
      }
    }

  }

  /**
   * eXo Alchemist enhancement behavior.
   */
  Drupal.behaviors.exoAlchemistEnhancementStack = {
    count: 0,
    instances: {},
    attach: function(context) {
      const self = this;
      $('.ee--stack-wrapper', context).once('exo.alchemist.enhancement').each(function () {
        const $wrapper = $(this);
        const id = $wrapper.data('ee--stack-id');
        $wrapper.data('ee--stack-count', self.count);
        self.instances[id + self.count] = new ExoAlchemistEnhancementStack(id, $wrapper);
        self.count++;
      });
    },
    detach: function detach(context, settings, trigger) {
      if (trigger === 'unload') {
        const self = this;
        $('.ee--stack-wrapper', context).each(function () {
          const $wrapper = $(this);
          const id = $wrapper.data('ee--stack-id') + $wrapper.data('ee--stack-count');
          if (typeof self.instances[id] !== 'undefined') {
            self.instances[id].unload();
            delete self.instances[id];
          }
        });
      }
    }
  }

})(jQuery, Drupal, Drupal.displace);
