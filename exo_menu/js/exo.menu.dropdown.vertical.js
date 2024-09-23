function _typeof(e){return(_typeof="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function _classCallCheck(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}function _defineProperties(e,t){for(var n=0;n<t.length;n++){var o=t[n];o.enumerable=o.enumerable||!1,o.configurable=!0,"value"in o&&(o.writable=!0),Object.defineProperty(e,_toPropertyKey(o.key),o)}}function _createClass(e,t,n){return t&&_defineProperties(e.prototype,t),n&&_defineProperties(e,n),Object.defineProperty(e,"prototype",{writable:!1}),e}function _toPropertyKey(e){e=_toPrimitive(e,"string");return"symbol"==_typeof(e)?e:e+""}function _toPrimitive(e,t){if("object"!=_typeof(e)||!e)return e;var n=e[Symbol.toPrimitive];if(void 0===n)return("string"===t?String:Number)(e);n=n.call(e,t||"default");if("object"!=_typeof(n))return n;throw new TypeError("@@toPrimitive must return a primitive value.")}function _callSuper(e,t,n){return t=_getPrototypeOf(t),_possibleConstructorReturn(e,_isNativeReflectConstruct()?Reflect.construct(t,n||[],_getPrototypeOf(e).constructor):t.apply(e,n))}function _possibleConstructorReturn(e,t){if(t&&("object"==_typeof(t)||"function"==typeof t))return t;if(void 0!==t)throw new TypeError("Derived constructors may only return object or undefined");return _assertThisInitialized(e)}function _assertThisInitialized(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}function _isNativeReflectConstruct(){try{var e=!Boolean.prototype.valueOf.call(Reflect.construct(Boolean,[],function(){}))}catch(e){}return(_isNativeReflectConstruct=function(){return!!e})()}function _superPropGet(e,t,n,o){var r=_get(_getPrototypeOf(1&o?e.prototype:e),t,n);return 2&o&&"function"==typeof r?function(e){return r.apply(n,e)}:r}function _get(){return(_get="undefined"!=typeof Reflect&&Reflect.get?Reflect.get.bind():function(e,t,n){var o=_superPropBase(e,t);if(o)return(o=Object.getOwnPropertyDescriptor(o,t)).get?o.get.call(arguments.length<3?e:n):o.value}).apply(null,arguments)}function _superPropBase(e,t){for(;!{}.hasOwnProperty.call(e,t)&&null!==(e=_getPrototypeOf(e)););return e}function _getPrototypeOf(e){return(_getPrototypeOf=Object.setPrototypeOf?Object.getPrototypeOf.bind():function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}function _inherits(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),Object.defineProperty(e,"prototype",{writable:!1}),t&&_setPrototypeOf(e,t)}function _setPrototypeOf(e,t){return(_setPrototypeOf=Object.setPrototypeOf?Object.setPrototypeOf.bind():function(e,t){return e.__proto__=t,e})(e,t)}(r=>{var e=(()=>{function t(){var e;return _classCallCheck(this,t),(e=_callSuper(this,t,arguments)).defaults={itemIcon:"",cloneExpandable:!1,expandActiveTrail:!1,transitionIn:"fadeIn",transitionOut:""},e}return _inherits(t,ExoMenuStyleBase),_createClass(t,[{key:"build",value:function(){var n,e,o=this;_superPropGet(t,"build",this,3)([]),this.get("cloneExpandable")&&this.$element.find(".expanded").each(function(e,t){var t=r(t),n=t.find("> .exo-menu-level > ul"),t=t.find("> a").clone();n.prepend(t),t.wrap("<li>")}),this.get("itemIcon")&&this.$element.find(".expanded > a").append(this.get("itemIcon")),(this.get("expandChildren")?this.$element.find(".level-0 > ul > .expanded > a"):this.$element.find(".expanded > a")).on("click.exo.menu.style.dropdown",function(e){var t=r(e.currentTarget);e.preventDefault(),o.toggle(t.closest(".expanded"))}),this.get("expandActiveTrail")&&(n=!1,e=this.$element.find(".expanded.active-trail"),this.$element.find(".expanded.active-trail").removeClass("active-trail"),this.$element.find(".is-active").each(function(e,t){r(t).parents(".expanded").each(function(e,t){n=!0,o.toggle(r(t),!1)})}),!1===n)&&e.each(function(e,t){o.toggle(r(t),!1)})}},{key:"toggle",value:function(e,t){t=!1!==t,e.hasClass("expand")?this.hide(e,t):this.show(e,t)}},{key:"show",value:function(e,t){var n=this,o=e.find("> .exo-menu-level");t=!1!==t,o.length&&(e.addClass("expand"),t)&&""!==this.get("transitionIn")&&void 0!==Drupal.Exo.animationEvent&&(o.off(Drupal.Exo.animationEvent+".exo.menu.hide"),o.removeClass("exo-animate-"+this.get("transitionOut")),o.addClass("exo-animate-"+this.get("transitionIn")),o.one(Drupal.Exo.animationEvent+".exo.menu.show",function(e){o.off(Drupal.Exo.animationEvent+".exo.menu.show"),o.removeClass("exo-animate-"+n.get("transitionIn"))}))}},{key:"hide",value:function(t,e){var n=this,o=t.find("> .exo-menu-level");e=!1!==e,o.length&&(e&&""!==this.get("transitionOut")&&void 0!==Drupal.Exo.animationEvent?(o.off(Drupal.Exo.animationEvent+".exo.menu.show"),o.removeClass("exo-animate-"+this.get("transitionIn")),o.addClass("exo-animate-"+this.get("transitionOut")),o.one(Drupal.Exo.animationEvent+".exo.menu.hide",function(e){t.removeClass("expand"),o.off(Drupal.Exo.animationEvent+".exo.menu.hide"),o.removeClass("exo-animate-"+n.get("transitionOut")),o.find(".expand").removeClass("expand")})):t.removeClass("expand"))}}])})();Drupal.ExoMenuStyles.dropdown_vertical=e})(jQuery);