function _typeof(t){return(_typeof="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t})(t)}function _defineProperties(t,e){for(var n=0;n<e.length;n++){var o=e[n];o.enumerable=o.enumerable||!1,o.configurable=!0,"value"in o&&(o.writable=!0),Object.defineProperty(t,_toPropertyKey(o.key),o)}}function _createClass(t,e,n){return e&&_defineProperties(t.prototype,e),n&&_defineProperties(t,n),Object.defineProperty(t,"prototype",{writable:!1}),t}function _toPropertyKey(t){t=_toPrimitive(t,"string");return"symbol"==_typeof(t)?t:t+""}function _toPrimitive(t,e){if("object"!=_typeof(t)||!t)return t;var n=t[Symbol.toPrimitive];if(void 0===n)return("string"===e?String:Number)(t);n=n.call(t,e||"default");if("object"!=_typeof(n))return n;throw new TypeError("@@toPrimitive must return a primitive value.")}function _classCallCheck(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}((r,s,u)=>{var o=_createClass(function t(e,n){var o=this,n=(_classCallCheck(this,t),this.id="",this.$wrapper=n,this.id=e,this.$wrapper.closest(".exo-component")),e=this.$wrapper.text().trim(),r=parseFloat(e.match(/[\d\.]+/i)[0]||""),i=e.match(/[^\d\.]*/i)[0]||"",a=e.match(/[\d\.]+(.*)/i)[1]||"",c=(e.match(/\./g)||[]).length;this.$wrapper.css({width:this.$wrapper.width()+"px",overflow:"visible"}),this.$wrapper.text(i+"0"+a);n.one(s.Exo.transitionEvent+".ash.count",function(t){new u.CountUp(o.$wrapper[0],r,{decimalPlaces:c,prefix:i,suffix:a,duration:3}).start()})});s.behaviors.exoAlchemistEnhancementCount={count:0,instances:{},attach:function(t){var n=this;r(".ee--count-wrapper",t).once("exo.alchemist.enhancement").each(function(){var t=r(this),e=t.data("ee--count-id");t.data("ee--count-count",n.count),n.instances[e+n.count]=new o(e,t),n.count++})},detach:function(t,e,n){var o;"unload"===n&&(o=this,r(".ee--count-wrapper",t).each(function(){var t=r(this),t=t.data("ee--count-id")+t.data("ee--count-count");void 0!==o.instances[t]&&(o.instances[t].unload(),delete o.instances[t])}))}}})(jQuery,Drupal,window.countUp);