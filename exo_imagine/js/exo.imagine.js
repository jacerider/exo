"use strict";function _typeof(e){return(_typeof="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function _classCallCheck(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}function _defineProperties(e,t){for(var n=0;n<t.length;n++){var i=t[n];i.enumerable=i.enumerable||!1,i.configurable=!0,"value"in i&&(i.writable=!0),Object.defineProperty(e,i.key,i)}}function _createClass(e,t,n){return t&&_defineProperties(e.prototype,t),n&&_defineProperties(e,n),e}!function(a,r,o){var s=function(){function o(e,t){var n=this;_classCallCheck(this,o),this.settings={webp:1,animate:1,blur:1,visible:1},this.$element=a(e),this.$image=this.$element.find(".exo-imagine-image"),this.$imageSources=this.$element.find(".exo-imagine-image-picture source"),this.$previewPicture=this.$element.find(".exo-imagine-preview-picture");var i=JSON.parse(this.$element.attr("data-exo-imagine"));"object"==_typeof(t.defaults)&&a.extend(this.settings,t.defaults),"object"==_typeof(i)&&a.extend(this.settings,i),this.settings.visible?r.Exo.trackElementPosition(this.$element.get(0),function(e){r.Exo.untrackElementPosition(e[0]),n.render()}):this.render(),this.$element.data("exo.imagine.init")}return _createClass(o,[{key:"render",value:function(){var n=this;return new Promise(function(e,t){n.$element.data("exo.imagine.loaded")||(n.$image.one("load",function(e){n.$element.addClass("exo-imagine-loaded"),n.settings.animate?(n.$previewPicture.one(r.Exo.transitionEvent,function(e){n.$previewPicture.remove()}),n.$element.addClass("exo-imagine-animate")):n.$previewPicture.remove()}),n.$imageSources.each(function(e,t){var n=a(t);n.attr("srcset",n.data("srcset")).removeAttr("data-srcset")}),n.$element.data("exo.imagine.loaded",!0),e())})}}]),o}();r.behaviors.exoImagine={supportsWebP:null,attach:function(e){void 0!==o.exoImagine&&a(".exo-imagine",e).once("exo.imagine").each(function(e,t){new s(t,o.exoImagine)})},render:function(n){return new Promise(function(t,e){var i=[];a(".exo-imagine",n).each(function(e,t){if(!a(t).data("exo.imagine.loaded")){var n=new s(t,o.exoImagine);i.push(n.render())}}),Promise.all(i).then(function(e){t()})})}}}(jQuery,Drupal,drupalSettings);