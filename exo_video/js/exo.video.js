"use strict";function _typeof(A){return(_typeof="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(A){return typeof A}:function(A){return A&&"function"==typeof Symbol&&A.constructor===Symbol&&A!==Symbol.prototype?"symbol":typeof A})(A)}function _classCallCheck(A,e){if(!(A instanceof e))throw new TypeError("Cannot call a class as a function")}function _defineProperties(A,e){for(var t=0;t<e.length;t++){var i=e[t];i.enumerable=i.enumerable||!1,i.configurable=!0,"value"in i&&(i.writable=!0),Object.defineProperty(A,i.key,i)}}function _createClass(A,e,t){return e&&_defineProperties(A.prototype,e),t&&_defineProperties(A,t),A}function _possibleConstructorReturn(A,e){return!e||"object"!==_typeof(e)&&"function"!=typeof e?_assertThisInitialized(A):e}function _assertThisInitialized(A){if(void 0===A)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return A}function _get(A,e,t){return(_get="undefined"!=typeof Reflect&&Reflect.get?Reflect.get:function(A,e,t){var i=_superPropBase(A,e);if(i){var o=Object.getOwnPropertyDescriptor(i,e);return o.get?o.get.call(t):o.value}})(A,e,t||A)}function _superPropBase(A,e){for(;!Object.prototype.hasOwnProperty.call(A,e)&&null!==(A=_getPrototypeOf(A)););return A}function _getPrototypeOf(A){return(_getPrototypeOf=Object.setPrototypeOf?Object.getPrototypeOf:function(A){return A.__proto__||Object.getPrototypeOf(A)})(A)}function _inherits(A,e){if("function"!=typeof e&&null!==e)throw new TypeError("Super expression must either be null or a function");A.prototype=Object.create(e&&e.prototype,{constructor:{value:A,writable:!0,configurable:!0}}),e&&_setPrototypeOf(A,e)}function _setPrototypeOf(A,e){return(_setPrototypeOf=Object.setPrototypeOf||function(A,e){return A.__proto__=e,A})(A,e)}var ExoVideoBase=function(){function o(A,e){var t;return _classCallCheck(this,o),(t=_possibleConstructorReturn(this,_getPrototypeOf(o).call(this,A))).ready=!1,t.expanded=!1,t.$wrapper=e,t}return _inherits(o,ExoData),_createClass(o,[{key:"build",value:function(t){var i=this;return new Promise(function(e,A){_get(_getPrototypeOf(o.prototype),"build",i).call(i,t).then(function(A){null!==A&&(i.setInnerWrapper(),i.make()),e(A)},A)})}},{key:"make",value:function(){this.$video=jQuery('<div id="'+this.getId()+'-video" class="exo-video-bg" style="transform: translate(-100vw, 0);"></div>').appendTo(this.$videoWrapper).css({position:"cover"===this.get("sizing")?"absolute":"relative"}),this.get("expanded")||this.$video.css({pointerEvents:"none"}),this.get("controls")&&this.makeControls()}},{key:"makeControls",value:function(){var t=this;if(void 0===this.$control){this.$control=jQuery('<div id="'+this.getId()+'-controls" class="exo-video-bg-control" style="display:none"></div>');var A=jQuery('<div class="exo-video-bg-toggle" tabindex="0"></div>').on("click",function(A){var e=jQuery(A.target);t.toggle(e)}).on("keydown",function(A){switch(A.which){case 13:case 32:A.preventDefault(),A.stopPropagation();var e=jQuery(A.target);t.toggle(e)}}).appendTo(this.$control);this.get("autoplay")?A.text("Pause").addClass("exo-video-bg-pause"):A.text("Play").addClass("exo-video-bg-play"),this.$control.appendTo(this.$wrapper)}}},{key:"toggle",value:function(A){A.hasClass("exo-video-bg-play")?(this.videoPlay(),A.text("Pause").removeClass("exo-video-bg-play").addClass("exo-video-bg-pause")):(this.videoPause(),A.text("Play").removeClass("exo-video-bg-pause").addClass("exo-video-bg-play"))}},{key:"setInnerWrapper",value:function(){var e=this;if(this.$videoWrapper=jQuery('<div class="exo-video-bg-wrapper"></div>').appendTo(this.$wrapper).css({zIndex:this.get("zIndex"),position:"cover"===this.get("sizing")?this.get("position"):"relative",width:"100%"}),"cover"===this.get("sizing")?this.$videoWrapper.css({top:0,left:0,right:0,bottom:0,overflow:"hidden"}):this.$videoWrapper.css({display:"flex",alignItems:"center",justifyContent:"center"}),this.makeImageBackground(),this.get("expand"))var t=jQuery('<a class="exo-video-bg-expand-open"><span>Open</span></a>').on("click.exo.video",function(A){Drupal.Exo.isMobile()?(t.hide(),e.videoRewind(),e.videoUnMute(),setTimeout(function(){e.$wrapper.on("click.exo.video",function(A){e.$wrapper.off("click.exo.video"),e.videoMute(),t.show()})})):e.videoExpand()}).appendTo(this.$videoWrapper);Drupal.Exo.trackElementPosition(this.$wrapper,function(A){setTimeout(function(){e.videoResize()})})}},{key:"makeImageBackground",value:function(){if(this.get("image")){var A={backgroundImage:"url("+this.get("image")+")",backgroundSize:this.get("sizing"),backgroundPosition:"center center",backgroundRepeat:"no-repeat"};this.$videoWrapper.css(A)}}},{key:"getWrapper",value:function(){return this.$wrapper}},{key:"videoReady",value:function(){Drupal.ExoVideo.onReady(this),this.ready=!0,this.$video=jQuery("#"+this.getId()+"-video"),this.get("mute")?this.videoMute():this.videoUnMute(),this.videoResizeBind()}},{key:"videoResizeBind",value:function(){var e=this;!1!==this.get("videoRatio")&&(Drupal.Exo.$window.on("resize.video-bg",{},Drupal.debounce(function(A){e.videoResize()},100)),this.videoResize())}},{key:"videoResize",value:function(){this.$video.css({width:"",height:""});var A=this.$videoWrapper.innerWidth(),e=this.$videoWrapper.innerHeight();0===e&&(e=A/this.get("videoRatio"));var t={},i=A,o=e;if("cover"===this.get("sizing")?(o=A/this.get("videoRatio"))<e&&(i=(o=e)*this.get("videoRatio")):(t.position="relative",o=A/this.get("videoRatio")),o=Math.ceil(o),i=Math.ceil(i),"cover"===this.get("sizing")){var n=Math.round(e/2-o/2),a=Math.round(A/2-i/2);t.top=n+"px",t.left=a+"px"}t.width=i+"px",t.height=o+"px",this.$video.css(t)}},{key:"videoWatch",value:function(){switch(this.get("autoplay")||this.videoPause(),this.get("expanded")&&this.videoContractBind(),this.get("when")){case"hover":this.videoPause(),this.videoHoverBind();break;case"viewport":this.videoViewportBind()}this.get("controls")&&this.$control.fadeIn()}},{key:"videoExpand",value:function(){var A=this.$wrapper.attr("id")+"-expand";this.$expand=this.$wrapper.clone(),this.$expand.html(""),this.$expand.attr("id",A),this.$expand.addClass("exo-video-bg-expand"),jQuery('<a class="exo-video-bg-expand-close"><span>Close</span></a>').appendTo(this.$expand),this.$expand.appendTo(Drupal.Exo.$exoCanvas).css({position:"fixed",top:0,right:0,bottom:0,left:0,zIndex:9999});var e=jQuery.extend(!0,{},this.getData(),{mute:!1,loop:!1,when:"always",expand:!1,expanded:!0,sizing:"contain"});Drupal.ExoVideo.create(A,e),Drupal.Exo.lockOverflow(this.$expand)}},{key:"videoContractBind",value:function(){var e=this;this.$videoWrapper.add(".exo-video-bg-expand-close",this.$wrapper[0]).on("click.exo.video",function(A){A.preventDefault(),Drupal.Exo.unlockOverflow(e.$wrapper),e.$wrapper.remove(),Drupal.ExoVideo.removeInstance(e.$wrapper.attr("id"))})}},{key:"videoHoverBind",value:function(){var e=this;this.$videoWrapper.on("mouseenter.exo.video",function(A){e.videoPlay()}).on("mouseleave.exo.video",function(A){e.videoPause()})}},{key:"videoViewportBind",value:function(){var A=this,e=setTimeout(function(){A.videoPause()},10);Drupal.Exo.trackElementPosition(this.$videoWrapper,function(){clearTimeout(e),A.videoPlay()},function(){clearTimeout(e),A.videoPause()})}},{key:"videoTime",value:function(){}},{key:"videoPlay",value:function(){}},{key:"videoPause",value:function(){}},{key:"videoRewind",value:function(){}},{key:"videoMute",value:function(){}},{key:"videoUnMute",value:function(){}}]),o}();!function(i,n){n.ExoVideoProviders={};var A=function(){function o(){var A;return _classCallCheck(this,o),(A=_possibleConstructorReturn(this,_getPrototypeOf(o).apply(this,arguments))).started=!1,A}return _inherits(o,ExoVideoBase),_createClass(o,[{key:"make",value:function(){var A=this;_get(_getPrototypeOf(o.prototype),"make",this).call(this),o.getApi().then(function(){A.videoBuild()})}},{key:"videoBuild",value:function(){var e=this;if(void 0===this.player){var A={loop:0,start:this.get("start"),autoplay:0,controls:0,disablekb:1,showinfo:0,playsinline:1,wmode:"transparent",iv_load_policy:3,modestbranding:1,rel:0,fs:0};this.player=new YT.Player(this.getId()+"-video",{height:"100%",width:"100%",playerVars:A,videoId:this.get("videoId"),events:{onReady:function(A){e.videoReady()},onStateChange:function(A){1===A.data&&!1===e.started&&(e.started=!0,e.$video.css("transform","").hide().fadeIn(),e.videoStartTimer()),0===A.data&&e.get("loop")&&(e.videoRewind(),e.videoMute(),e.videoPlay())}}})}}},{key:"videoReady",value:function(){_get(_getPrototypeOf(o.prototype),"videoReady",this).call(this),this.videoPlay(),this.videoWatch()}},{key:"videoStartTimer",value:function(){var A=this;n.ExoVideo.onTimeUpdate(this),!this.get("loop")&&this.videoTime().toFixed(2)>this.player.getDuration().toFixed(2)-.4?(this.$video.css("transform","").fadeOut(400),this.$videoWrapper.addClass("loop-stop")):setTimeout(function(){A.videoStartTimer()},100)}},{key:"videoTime",value:function(){return this.player.getCurrentTime()}},{key:"videoPlay",value:function(){return this.player.playVideo()}},{key:"videoPause",value:function(){return this.player.pauseVideo()}},{key:"videoRewind",value:function(){return this.player.seekTo(0)}},{key:"videoMute",value:function(){return this.player.mute()}},{key:"videoUnMute",value:function(){return this.player.unMute()}}],[{key:"getApi",value:function(){return new Promise(function(A,e){if(0===o.apiState){o.apiState=1;var t=document.createElement("script");t.src="https://www.youtube.com/iframe_api";var i=document.getElementsByTagName("script")[0];i.parentNode.insertBefore(t,i),window.onYouTubeIframeAPIReady=function(){o.apiState=2,A(),n.Exo.$document.trigger("exo-video-youtube-ready")}}else 1===o.apiState?n.Exo.$document.one("exo-video-youtube-ready",function(){A()}):A()})}}]),o}();A.apiState=0,n.ExoVideoProviders.youtube=A;var e=function(){function o(){var A;return _classCallCheck(this,o),(A=_possibleConstructorReturn(this,_getPrototypeOf(o).apply(this,arguments))).started=!1,A.time=0,A}return _inherits(o,ExoVideoBase),_createClass(o,[{key:"make",value:function(){var A=this;_get(_getPrototypeOf(o.prototype),"make",this).call(this),o.getApi().then(function(){A.videoBuild()})}},{key:"videoBuild",value:function(){var A=this;if(void 0===this.player){var e={id:this.get("videoId"),autoplay:!0,background:!1===this.get("expanded"),controls:!1===this.get("expanded"),loop:this.get("loop"),byline:!1,portrait:!1};this.player=new Vimeo.Player(this.getId()+"-video",e),this.player.ready().then(function(){A.videoReady()})}}},{key:"videoReady",value:function(){_get(_getPrototypeOf(o.prototype),"videoReady",this).call(this),this.$video.find("iframe").css({width:"100%",height:"100%"}).removeAttr("width").removeAttr("height"),this.videoResize(),this.videoPrepare()}},{key:"videoPrepare",value:function(){var e=this;this.get("expanded")&&(this.$video.hide().css("transform","").fadeIn(),this.videoWatch()),this.player.on("timeupdate",function(A){e.time=A.seconds,2.3<e.time&&(e.player.off("timeupdate"),e.$video.hide().css("transform","").fadeIn(),e.videoWatch())})}},{key:"videoTime",value:function(){return this.time}},{key:"videoPlay",value:function(){return this.player.play()}},{key:"videoPause",value:function(){return this.player.pause()}},{key:"videoRewind",value:function(){return this.player.setCurrentTime(0)}},{key:"videoMute",value:function(){return this.player.setVolume(0)}},{key:"videoUnMute",value:function(){return this.player.setVolume(1)}}],[{key:"getApi",value:function(){return new Promise(function(A,e){if(0===o.apiState){o.apiState=1;var t=document.createElement("script");t.src="https://player.vimeo.com/api/player.js",t.onload=function(){o.apiState=2,A(),n.Exo.$document.trigger("exo-video-vimeo-ready")};var i=document.getElementsByTagName("script")[0];i.parentNode.insertBefore(t,i)}else 1===o.apiState?n.Exo.$document.one("exo-video-vimeo-ready",function(){A()}):A()})}}]),o}();e.apiState=0,n.ExoVideoProviders.vimeo=e;var t=function(){function e(){var A;return _classCallCheck(this,e),(A=_possibleConstructorReturn(this,_getPrototypeOf(e).apply(this,arguments))).label="ExoVideo",A.settingsGroup="exoVideo",A.instanceSettingsGroup="videos",A.autoplaySupported=null,A.events={ready:new ExoEvent,timeupdate:new ExoEvent},A}return _inherits(e,ExoDataManager),_createClass(e,[{key:"createInstance",value:function(A,e){if(void 0!==n.ExoVideoProviders[e.provider]){var t=i("#"+A);if(t.length)return new n.ExoVideoProviders[e.provider](A,t)}return!1}},{key:"videoResize",value:function(){this.getInstances().each(function(A){A.videoResize()})}},{key:"onReady",value:function(A){this.event("ready").trigger(A)}},{key:"onTimeUpdate",value:function(A){this.event("timeupdate").trigger(A)}},{key:"event",value:function(A){return void 0!==this.events[A]?this.events[A].expose():null}},{key:"detectAutoplay",value:function(a){var r=this;return this.autoplayPromise||(this.autoplayPromise=new Promise(function(A,e){if(null!==r.autoplaySupported)return r.autoplaySupported;var t=document.createElement("video"),i=document.createElement("source");i.src="data:video/mp4;base64,AAAAFGZ0eXBNU05WAAACAE1TTlYAAAOUbW9vdgAAAGxtdmhkAAAAAM9ghv7PYIb+AAACWAAACu8AAQAAAQAAAAAAAAAAAAAAAAEAAAAAAAAAAAAAAAAAAAABAAAAAAAAAAAAAAAAAABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAgAAAnh0cmFrAAAAXHRraGQAAAAHz2CG/s9ghv4AAAABAAAAAAAACu8AAAAAAAAAAAAAAAAAAAAAAAEAAAAAAAAAAAAAAAAAAAABAAAAAAAAAAAAAAAAAABAAAAAAFAAAAA4AAAAAAHgbWRpYQAAACBtZGhkAAAAAM9ghv7PYIb+AAALuAAANq8AAAAAAAAAIWhkbHIAAAAAbWhscnZpZGVBVlMgAAAAAAABAB4AAAABl21pbmYAAAAUdm1oZAAAAAAAAAAAAAAAAAAAACRkaW5mAAAAHGRyZWYAAAAAAAAAAQAAAAx1cmwgAAAAAQAAAVdzdGJsAAAAp3N0c2QAAAAAAAAAAQAAAJdhdmMxAAAAAAAAAAEAAAAAAAAAAAAAAAAAAAAAAFAAOABIAAAASAAAAAAAAAABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAGP//AAAAEmNvbHJuY2xjAAEAAQABAAAAL2F2Y0MBTUAz/+EAGGdNQDOadCk/LgIgAAADACAAAAMA0eMGVAEABGjuPIAAAAAYc3R0cwAAAAAAAAABAAAADgAAA+gAAAAUc3RzcwAAAAAAAAABAAAAAQAAABxzdHNjAAAAAAAAAAEAAAABAAAADgAAAAEAAABMc3RzegAAAAAAAAAAAAAADgAAAE8AAAAOAAAADQAAAA0AAAANAAAADQAAAA0AAAANAAAADQAAAA0AAAANAAAADQAAAA4AAAAOAAAAFHN0Y28AAAAAAAAAAQAAA7AAAAA0dXVpZFVTTVQh0k/Ou4hpXPrJx0AAAAAcTVREVAABABIAAAAKVcQAAAAAAAEAAAAAAAAAqHV1aWRVU01UIdJPzruIaVz6ycdAAAAAkE1URFQABAAMAAAAC1XEAAACHAAeAAAABBXHAAEAQQBWAFMAIABNAGUAZABpAGEAAAAqAAAAASoOAAEAZABlAHQAZQBjAHQAXwBhAHUAdABvAHAAbABhAHkAAAAyAAAAA1XEAAEAMgAwADAANQBtAGUALwAwADcALwAwADYAMAA2ACAAMwA6ADUAOgAwAAABA21kYXQAAAAYZ01AM5p0KT8uAiAAAAMAIAAAAwDR4wZUAAAABGjuPIAAAAAnZYiAIAAR//eBLT+oL1eA2Nlb/edvwWZflzEVLlhlXtJvSAEGRA3ZAAAACkGaAQCyJ/8AFBAAAAAJQZoCATP/AOmBAAAACUGaAwGz/wDpgAAAAAlBmgQCM/8A6YEAAAAJQZoFArP/AOmBAAAACUGaBgMz/wDpgQAAAAlBmgcDs/8A6YEAAAAJQZoIBDP/AOmAAAAACUGaCQSz/wDpgAAAAAlBmgoFM/8A6YEAAAAJQZoLBbP/AOmAAAAACkGaDAYyJ/8AFBAAAAAKQZoNBrIv/4cMeQ==";var o=document.createElement("source");o.src="data:video/webm;base64,GkXfo49CgoR3ZWJtQoeBAUKFgQEYU4BnAQAAAAAAF60RTZt0vE27jFOrhBVJqWZTrIIQA027jFOrhBZUrmtTrIIQbE27jFOrhBFNm3RTrIIXmU27jFOrhBxTu2tTrIIWs+xPvwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAFUmpZuQq17GDD0JATYCjbGliZWJtbCB2MC43LjcgKyBsaWJtYXRyb3NrYSB2MC44LjFXQY9BVlNNYXRyb3NrYUZpbGVEiYRFnEAARGGIBc2Lz1QNtgBzpJCy3XZ0KNuKNZS4+fDpFxzUFlSua9iu1teBAXPFhL4G+bmDgQG5gQGIgQFVqoEAnIEAbeeBASMxT4Q/gAAAVe6BAIaFVl9WUDiqgQEj44OEE95DVSK1nIN1bmTgkbCBULqBPJqBAFSwgVBUuoE87EQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB9DtnVB4eeBAKC4obaBAAAAkAMAnQEqUAA8AABHCIWFiIWEiAICAAamYnoOC6cfJa8f5Zvda4D+/7YOf//nNefQYACgnKGWgQFNANEBAAEQEAAYABhYL/QACIhgAPuC/rOgnKGWgQKbANEBAAEQEAAYABhYL/QACIhgAPuC/rKgnKGWgQPoANEBAAEQEAAYABhYL/QACIhgAPuC/rOgnKGWgQU1ANEBAAEQEAAYABhYL/QACIhgAPuC/rOgnKGWgQaDANEBAAEQEAAYABhYL/QACIhgAPuC/rKgnKGWgQfQANEBAAEQEAAYABhYL/QACIhgAPuC/rOgnKGWgQkdANEBAAEQEBRgAGFgv9AAIiGAAPuC/rOgnKGWgQprANEBAAEQEAAYABhYL/QACIhgAPuC/rKgnKGWgQu4ANEBAAEQEAAYABhYL/QACIhgAPuC/rOgnKGWgQ0FANEBAAEQEAAYABhYL/QACIhgAPuC/rOgnKGWgQ5TANEBAAEQEAAYABhYL/QACIhgAPuC/rKgnKGWgQ+gANEBAAEQEAAYABhYL/QACIhgAPuC/rOgnKGWgRDtANEBAAEQEAAYABhYL/QACIhgAPuC/rOgnKGWgRI7ANEBAAEQEAAYABhYL/QACIhgAPuC/rIcU7trQOC7jLOBALeH94EB8YIUzLuNs4IBTbeH94EB8YIUzLuNs4ICm7eH94EB8YIUzLuNs4ID6LeH94EB8YIUzLuNs4IFNbeH94EB8YIUzLuNs4IGg7eH94EB8YIUzLuNs4IH0LeH94EB8YIUzLuNs4IJHbeH94EB8YIUzLuNs4IKa7eH94EB8YIUzLuNs4ILuLeH94EB8YIUzLuNs4INBbeH94EB8YIUzLuNs4IOU7eH94EB8YIUzLuNs4IPoLeH94EB8YIUzLuNs4IQ7beH94EB8YIUzLuNs4ISO7eH94EB8YIUzBFNm3SPTbuMU6uEH0O2dVOsghTM",t.appendChild(o),t.appendChild(i),t.id="base64_test_video",t.autoplay=!0,t.style.position="fixed",t.style.left="5000px",document.getElementsByTagName("body")[0].appendChild(t);var n=document.getElementById("base64_test_video");setTimeout(function(){r.autoplaySupported=!n.paused,A(r.autoplaySupported),document.getElementsByTagName("body")[0].removeChild(t)},a)})),this.autoplayPromise}}]),e}();n.ExoVideo=new t,n.behaviors.exoVideo={attach:function(A){n.ExoVideo.attach(A)}}}(jQuery,Drupal,Drupal.debounce);