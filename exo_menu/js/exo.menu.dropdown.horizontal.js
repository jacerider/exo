"use strict";function _typeof(e){return(_typeof="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function _classCallCheck(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}function _defineProperties(e,t){for(var n=0;n<t.length;n++){var o=t[n];o.enumerable=o.enumerable||!1,o.configurable=!0,"value"in o&&(o.writable=!0),Object.defineProperty(e,o.key,o)}}function _createClass(e,t,n){return t&&_defineProperties(e.prototype,t),n&&_defineProperties(e,n),e}function _possibleConstructorReturn(e,t){return!t||"object"!==_typeof(t)&&"function"!=typeof t?_assertThisInitialized(e):t}function _assertThisInitialized(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}function _get(e,t,n){return(_get="undefined"!=typeof Reflect&&Reflect.get?Reflect.get:function(e,t,n){var o=_superPropBase(e,t);if(o){var r=Object.getOwnPropertyDescriptor(o,t);return r.get?r.get.call(n):r.value}})(e,t,n||e)}function _superPropBase(e,t){for(;!Object.prototype.hasOwnProperty.call(e,t)&&null!==(e=_getPrototypeOf(e)););return e}function _getPrototypeOf(e){return(_getPrototypeOf=Object.setPrototypeOf?Object.getPrototypeOf:function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}function _inherits(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),t&&_setPrototypeOf(e,t)}function _setPrototypeOf(e,t){return(_setPrototypeOf=Object.setPrototypeOf||function(e,t){return e.__proto__=t,e})(e,t)}!function(l){var e=function(){function o(){var e;return _classCallCheck(this,o),(e=_possibleConstructorReturn(this,_getPrototypeOf(o).apply(this,arguments))).defaults={itemIcon:"",expandable:!0,unbindFirst:!1,transitionIn:"expandInY",transitionOut:"expandOutY"},e.linkSelector=".exo-menu-link",e}return _inherits(o,ExoMenuStyleBase),_createClass(o,[{key:"build",value:function(){var s=this;_get(_getPrototypeOf(o.prototype),"build",this).call(this);var e=this.get("expandable"),t=this.get("unbindFirst"),n=this.$element.find(this.linkSelector);this.$element.attr("role","menubar"),n.each(function(e,t){var n=l(t);n.closest("li").hasClass("expanded")&&(n.attr("aria-haspopup","true"),n.attr("aria-expanded","false"))}).on("keydown.exo.menu.style.dropdown",function(e){var t,n=l(e.currentTarget).closest("li"),o=n.parent().closest(".expand"),r=n.hasClass("expanded"),a=o.length;switch(e.which){case 27:a&&(e.preventDefault(),e.stopPropagation(),s.hide(o),o.find("> "+s.linkSelector+":first").trigger("focus"));break;case 39:a||(e.preventDefault(),e.stopPropagation(),s.hide(n),(t=n.next("li")).find(s.linkSelector+":first").trigger("focus"),t.hasClass("expanded")&&s.show(t));break;case 40:r&&(e.preventDefault(),e.stopPropagation(),s.show(n),n.find("> .exo-menu-level "+s.linkSelector+":first").trigger("focus")),a&&(e.preventDefault(),e.stopPropagation(),n.next("li").find(s.linkSelector+":first").trigger("focus"));break;case 37:a||(e.preventDefault(),e.stopPropagation(),s.hide(n),(t=n.prev("li")).find(s.linkSelector+":first").trigger("focus"),t.hasClass("expanded")&&s.show(t));break;case 38:if(a){var i=n.prev("li");i.length?i.find(s.linkSelector).first().trigger("focus"):(s.hide(o),o.find("> "+s.linkSelector).first().trigger("focus"))}}}),this.get("itemIcon")&&(e?this.$element.find(".expanded > a").append(this.get("itemIcon")):this.$element.find(".level-0 > ul > .expanded > a").append(this.get("itemIcon"))),t?(this.$element.find(".level-0 > ul > .expanded.active-trail").addClass("no-event").addClass("expand"),this.$element.find(".level-1 > ul > .expanded").on("mouseenter.exo.menu.style.dropdown",function(e){var t=l(e.currentTarget);clearTimeout(t.data("timeout"));var n=setTimeout(function(){Drupal.Exo.getBodyElement().addClass("exo-menu-expanded"),s.show(l(e.currentTarget))},200);t.data("timeout",n)}).on("mouseleave.exo.menu.style.dropdown",function(e){var t=l(e.currentTarget);clearTimeout(t.data("timeout")),Drupal.Exo.getBodyElement().removeClass("exo-menu-expanded"),s.hide(l(e.currentTarget))})):this.$element.find(".level-0 > ul > .expanded").on("mouseenter.exo.menu.style.dropdown",function(e){var t=l(e.currentTarget);clearTimeout(t.data("timeout"));var n=setTimeout(function(){Drupal.Exo.getBodyElement().addClass("exo-menu-expanded"),s.show(l(e.currentTarget))},200);t.data("timeout",n)}).on("mouseleave.exo.menu.style.dropdown",function(e){var t=l(e.currentTarget);clearTimeout(t.data("timeout")),Drupal.Exo.getBodyElement().removeClass("exo-menu-expanded"),s.hide(l(e.currentTarget))}),e?this.$element.find(".level-1 .expanded > a").on("click.exo.menu.style.dropdown",function(e){e.preventDefault(),s.toggle(l(e.target).closest(".expanded"),!1)}):this.$element.find(".level-1 .expanded").addClass("expand")}},{key:"toggle",value:function(e,t){t=!1!==t,e.hasClass("expand")?this.hide(e,t):this.show(e,t)}},{key:"show",value:function(e,t){var n=this,o=e.find("> .exo-menu-level");t=!1!==t,o.length&&!e.hasClass("expand")&&(e.addClass("expand"),e.find(this.linkSelector+":first").attr("aria-expanded","true"),t&&""!==this.get("transitionIn")&&void 0!==Drupal.Exo.animationEvent&&(o.off(Drupal.Exo.animationEvent+".exo.menu.hide"),o.removeClass("exo-animate-"+this.get("transitionOut")),o.addClass("exo-animate-"+this.get("transitionIn")),o.one(Drupal.Exo.animationEvent+".exo.menu.show",function(e){o.off(Drupal.Exo.animationEvent+".exo.menu.show"),o.removeClass("exo-animate-"+n.get("transitionIn"))})))}},{key:"hide",value:function(t,e){var n=this,o=t.find("> .exo-menu-level");e=!1!==e,o.length&&(e&&""!==this.get("transitionOut")&&void 0!==Drupal.Exo.animationEvent?(o.off(Drupal.Exo.animationEvent+".exo.menu.show"),o.removeClass("exo-animate-"+this.get("transitionIn")),o.addClass("exo-animate-"+this.get("transitionOut")),o.one(Drupal.Exo.animationEvent+".exo.menu.hide",function(e){t.removeClass("expand"),o.off(Drupal.Exo.animationEvent+".exo.menu.hide"),o.removeClass("exo-animate-"+n.get("transitionOut")),n.get("expandable")&&o.find(".expand").removeClass("expand")})):t.removeClass("expand"),t.find(this.linkSelector+":first").attr("aria-expanded","false"))}}]),o}();Drupal.ExoMenuStyles.dropdown_horizontal=e}(jQuery);