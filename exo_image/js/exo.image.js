function _typeof(e){return(_typeof="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function _classCallCheck(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}function _defineProperties(e,t){for(var i=0;i<t.length;i++){var a=t[i];a.enumerable=a.enumerable||!1,a.configurable=!0,"value"in a&&(a.writable=!0),Object.defineProperty(e,_toPropertyKey(a.key),a)}}function _createClass(e,t,i){return t&&_defineProperties(e.prototype,t),i&&_defineProperties(e,i),Object.defineProperty(e,"prototype",{writable:!1}),e}function _toPropertyKey(e){e=_toPrimitive(e,"string");return"symbol"==_typeof(e)?e:e+""}function _toPrimitive(e,t){if("object"!=_typeof(e)||!e)return e;var i=e[Symbol.toPrimitive];if(void 0===i)return("string"===t?String:Number)(e);i=i.call(e,t||"default");if("object"!=_typeof(i))return i;throw new TypeError("@@toPrimitive must return a primitive value.")}var ExoImage=(()=>_createClass(function e(){_classCallCheck(this,e),this.isBuilt=!1,this.supportsWebP=!1,this.defaults={ratio_distortion:60,upscale:320,downscale:1900,multiplier:1,animate:1,bg:0,visible:1,handler:"scale",ratio:{width:1,height:1}}},[{key:"attach",value:function(i){var a=this;return new Promise(function(e,t){!1===a.isBuilt&&(a.build(i),!0===drupalSettings.exoImage.defaults.webp)?a.checkSupportForWebP().then(function(){a.supportsWebP=!0,a.init(i),e(!0)},function(){a.init(i),e(!0)}):(a.init(i),e(!0))})}},{key:"build",value:function(e){var t=this;this.isBuilt=!0,Drupal.Exo.event("breakpoint").on("exo.image",function(e){t.init()}),Drupal.Exo.$window.on("resize.exo.image",_.debounce(function(){"xlarge"===Drupal.Exo.breakpoint.name&&t.init()},200)),this.triggerResize=_.debounce(function(){Drupal.Exo.$window.trigger("resize")},200)}},{key:"init",value:function(e){var t=this,i=(e=void 0===e?document:e).querySelectorAll(".exo-image");if(0<i.length)for(var a=0;a<i.length;a++)this.isHidden(i[a])||(1===this.fetchData(i[a]).visible&&null===i[a].getAttribute("data-w")?Drupal.Exo.trackElementPosition(i[a],function(e){Drupal.Exo.untrackElementPosition(e[0]),t.renderEl(e[0])}):this.renderEl(i[a]))}},{key:"isHidden",value:function(e){return"none"===window.getComputedStyle(e).display}},{key:"fetchData",value:function(e){var t,i,a=JSON.parse(e.getAttribute("data-exo-image"));for(t in drupalSettings.exoImage.defaults)drupalSettings.exoImage.defaults.hasOwnProperty(t)&&void 0===a[t]&&(a[t]=drupalSettings.exoImage.defaults[t]);for(i in this.defaults)this.defaults.hasOwnProperty(i)&&void 0===a[i]&&(a[i]=this.defaults[i]);return Drupal.Exo.isIE()&&(a.animate=0,a.visible=0),0===e.offsetWidth&&(a.visible=1),Drupal.Exo.cleanData(a,this.defaults)}},{key:"renderEl",value:function(i){var e,t,a,o,n,r,u,l=this.fetchData(i);!1===isNaN(l.fid)&&l.fid%1==0&&0<Number(l.fid)&&(e=this.size(i),t=Number(i.getAttribute("data-w")),a=Number(i.getAttribute("data-h")),e[0]===t&&e[1]===a||0<e[0]&&(o=i.querySelector&&i.querySelector("img.exo-image-preview"),n="/images/"+e[0]+"/"+e[1]+"/"+l.fid+"/"+encodeURIComponent(l.filename),this.supportsWebP&&(n=n.substr(0,n.lastIndexOf("."))+".webp"),i.setAttribute("data-w",e[0].toString()),i.setAttribute("data-h",e[1].toString()),r=0,(u=new Image).className="exo-image-reveal exo-image-actual",u.width=e[0],u.height=e[1],u.onload=function(e){var t=i.querySelector&&i.querySelector("img.exo-image-actual");t&&(t.parentNode.removeChild(t),l.animate=0),i.appendChild(u),1===l.bg?(u.style.visibility="hidden",u.classList.remove("exo-image-reveal"),i.style.backgroundImage='url("'+n+'")',1===l.animate?setTimeout(function(){o.classList.add("exo-image-fadeout"),o.addEventListener(Drupal.Exo.animationEvent,function(e){o.parentNode.removeChild(o),o.removeEventListener(Drupal.Exo.animationEvent,function(e){})})},50):o&&setTimeout(function(){o.parentNode.removeChild(o)},50)):1===l.animate?(o&&o.classList.add("exo-image-float"),u.addEventListener(Drupal.Exo.animationEvent,function(e){o&&o.parentNode.removeChild(o),u.removeEventListener(Drupal.Exo.animationEvent,function(e){}),u.classList.remove("exo-image-reveal")})):(o&&o.parentNode.removeChild(o),u.classList.remove("exo-image-reveal"))},u.onerror=function(e){++r<3&&u.setAttributeNS("http://www.w3.org/1999/xlink","href",n)},u.src=n,this.triggerResize()))}},{key:"size",value:function(e){var t,i,a;return 0===e.offsetWidth?{0:0,1:0}:(t=this.fetchData(e),i=Drupal.Exo.getPxFromEm(Drupal.Exo.getMeasurementValue(Drupal.Exo.breakpoint.max)),a=document.documentElement.clientWidth>i?i:document.documentElement.clientWidth,a=Math.round(e.offsetWidth/a*10)/10,i={0:Math.ceil(i*a),1:0},"xlarge"===Drupal.Exo.breakpoint.name&&(i[0]=Math.round(100*e.offsetWidth)/100),i[0]===document.documentElement.clientWidth&&(i[0]=Drupal.Exo.getPxFromEm(Drupal.Exo.breakpoint.max)),(a=1)===t.multiplier&&(a=Number(window.devicePixelRatio),!0===isNaN(a)||a<=0)&&(a=1),i[0]=Math.round(i[0]*a),(i=(i=i[0]<t.upscale?this.resize(i,t.upscale,0):i)[0]>t.downscale?this.resize(i,t.downscale,0):i)[0]=20*Math.ceil(i[0]/20),"ratio"===t.handler&&(i[1]=Math.round(i[0]*t.thumb_ratio)),i)}},{key:"resize",value:function(e,t,i){var a;return 0===e[i]?e:((a={0:e[0],1:e[1]})[i]=t,0!==e[t=Math.abs(i-1)]&&(a[t]=Math.round(a[t]*(a[i]/e[i]))),a)}},{key:"checkSupportForWebP",value:function(a){var o={basic:"data:image/webp;base64,UklGRjIAAABXRUJQVlA4ICYAAACyAgCdASoCAAEALmk0mk0iIiIiIgBoSygABc6zbAAA/v56QAAAAA==",lossless:"data:image/webp;base64,UklGRh4AAABXRUJQVlA4TBEAAAAvAQAAAAfQ//73v/+BiOh/AAA="};return new Promise(function(e,t){var i=new Image;i.onload=function(){e(!0)},i.onerror=function(){t()},i.src=o[a||"basic"]})}}]))();Drupal.ExoImage=new ExoImage,(t=>{t.behaviors.exoImage={attach:function(e){t.ExoImage.attach(e)}}})(Drupal,drupalSettings);