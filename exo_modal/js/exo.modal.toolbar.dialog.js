"use strict";function _typeof(e){return(_typeof="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function _classCallCheck(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}function _defineProperties(e,t){for(var o=0;o<t.length;o++){var n=t[o];n.enumerable=n.enumerable||!1,n.configurable=!0,"value"in n&&(n.writable=!0),Object.defineProperty(e,n.key,n)}}function _createClass(e,t,o){return t&&_defineProperties(e.prototype,t),o&&_defineProperties(e,o),e}function _possibleConstructorReturn(e,t){return!t||"object"!==_typeof(t)&&"function"!=typeof t?_assertThisInitialized(e):t}function _assertThisInitialized(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}function _get(e,t,o){return(_get="undefined"!=typeof Reflect&&Reflect.get?Reflect.get:function(e,t,o){var n=_superPropBase(e,t);if(n){var r=Object.getOwnPropertyDescriptor(n,t);return r.get?r.get.call(o):r.value}})(e,t,o||e)}function _superPropBase(e,t){for(;!Object.prototype.hasOwnProperty.call(e,t)&&null!==(e=_getPrototypeOf(e)););return e}function _getPrototypeOf(e){return(_getPrototypeOf=Object.setPrototypeOf?Object.getPrototypeOf:function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}function _inherits(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),t&&_setPrototypeOf(e,t)}function _setPrototypeOf(e,t){return(_setPrototypeOf=Object.setPrototypeOf||function(e,t){return e.__proto__=t,e})(e,t)}!function(a,s){var e=function(){function l(){return _classCallCheck(this,l),_possibleConstructorReturn(this,_getPrototypeOf(l).apply(this,arguments))}return _inherits(l,ExoToolbarDialogTypeBase),_createClass(l,[{key:"build",value:function(o,n,r){var i=this;return new Promise(function(t,e){_get(_getPrototypeOf(l.prototype),"build",i).call(i,o,n,r).then(function(){i.$element&&(s.detachBehaviors(i.$element.get(0),i.settings),i.$element.remove()),i.$element=a(n.data),i.$element.addClass(s.ExoToolbarDialog.dialogItemContentClass),i.$element.appendTo("body"),s.attachBehaviors(i.$element.get(0),i.settings),s.ExoModal.isReady().then(function(e){i.exoModal=s.ExoModal.getInstance(i.settings.exo_modal_id),t()})})})}},{key:"show",value:function(){var t=this;return _get(_getPrototypeOf(l.prototype),"show",this).call(this),this.exoToolbarItem.disableAside(),this.exoModal.event("closing").on("exo.modal.dialog.type",function(e){t.exoToolbarDialogItem.hide()}),setTimeout(function(){s.ExoModal.getWrapper().css("z-index",t.exoToolbarItem.getRegion().zIndexGet("HOVER")),t.exoModal.open()}),this}},{key:"hide",value:function(){var t=this;return _get(_getPrototypeOf(l.prototype),"hide",this).call(this),this.exoToolbarItem.enableAside(),this.exoModal.event("closing").off("exo.modal.dialog.type"),this.exoModal.event("closed").on("exo.modal.dialog.type",function(e){s.ExoModal.getWrapper().css("z-index"),t.exoModal.event("closed").off("exo.modal.dialog.type")}),this.exoModal.close(),this}}]),l}();s.ExoToolbarDialogTypes.modal=e}(jQuery,Drupal,drupalSettings);