function _typeof(e){return(_typeof="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function _classCallCheck(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}function _defineProperties(e,t){for(var r=0;r<t.length;r++){var o=t[r];o.enumerable=o.enumerable||!1,o.configurable=!0,"value"in o&&(o.writable=!0),Object.defineProperty(e,_toPropertyKey(o.key),o)}}function _createClass(e,t,r){return t&&_defineProperties(e.prototype,t),r&&_defineProperties(e,r),Object.defineProperty(e,"prototype",{writable:!1}),e}function _toPropertyKey(e){e=_toPrimitive(e,"string");return"symbol"==_typeof(e)?e:e+""}function _toPrimitive(e,t){if("object"!=_typeof(e)||!e)return e;var r=e[Symbol.toPrimitive];if(void 0===r)return("string"===t?String:Number)(e);r=r.call(e,t||"default");if("object"!=_typeof(r))return r;throw new TypeError("@@toPrimitive must return a primitive value.")}(e=>{var t=(()=>_createClass(function e(){_classCallCheck(this,e)},[{key:"getHash",value:function(){return window.location.hash}},{key:"getHashAsObject",value:function(e){var t=this.getHash(),r={};if(t)for(var o=t.replace("#","").split("+"),n=0;n<o.length;n++)if(!1!==/^([0-9a-zA-Z+/]{4})*(([0-9a-zA-Z+/]{2}==)|([0-9a-zA-Z+/]{3}=))?$/.test(o[n])){var i=atob(o[n]);if(i.substring(0,4)===e.substring(0,4)){var a=i.split("~");if(void 0!==a[1])for(var s=a[1].split("|"),l=0;l<s.length;l++){var u=s[l].split("--");1<u.length?(r[a[0]]={},r[a[0]][u[0]]=u[1]):r[a[0]]=u[0]}}}return r}},{key:"setHash",value:function(e){"#"+e!==window.location.hash&&(history.pushState?history.pushState({hash:e},null,"#"+e):window.location.hash=e)}},{key:"setHashAsObject",value:function(e){var t,r="";for(t in e)if(Object.prototype.hasOwnProperty.call(e,t)){var o=e[t],n=(""!==r&&(r+="+"),t+"~");if("object"===_typeof(o)){var i,a="";for(i in o)Object.prototype.hasOwnProperty.call(o,i)&&(""!==a&&(a+="|"),a+=i+"--"+o[i]);n+=a}else n+=o;r+=btoa(n)}return this.setHash(r),this}},{key:"getHashForKey",value:function(e){var t=this.getHashAsObject(e);return void 0!==t[e]?t[e]:null}},{key:"setHashForKey",value:function(e,t,r){var o=this.getHashAsObject(e);return r?(void 0===o[e]&&(o[e]={}),o[e][r]=t):o[e]=t,this.setHashAsObject(o)}},{key:"removeHashForKey",value:function(e,t,r){var o=this.getHashAsObject(e);if(void 0!==o[e])if(r){delete o[e][r];var n,i=!0;for(n in o[e])Object.prototype.hasOwnProperty.call(o,n)&&(i=!1);!0===i&&delete o[e]}else delete o[e];return this.setHashAsObject(o)}}]))();e.ExoAlchemistEnhancement=new t})((jQuery,Drupal));