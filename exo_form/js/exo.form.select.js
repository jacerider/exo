"use strict";function _typeof(e){return(_typeof="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function _classCallCheck(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}function _defineProperties(e,t){for(var i=0;i<t.length;i++){var o=t[i];o.enumerable=o.enumerable||!1,o.configurable=!0,"value"in o&&(o.writable=!0),Object.defineProperty(e,_toPropertyKey(o.key),o)}}function _createClass(e,t,i){return t&&_defineProperties(e.prototype,t),i&&_defineProperties(e,i),Object.defineProperty(e,"prototype",{writable:!1}),e}function _toPropertyKey(e){e=_toPrimitive(e,"string");return"symbol"===_typeof(e)?e:String(e)}function _toPrimitive(e,t){if("object"!==_typeof(e)||null===e)return e;var i=e[Symbol.toPrimitive];if(void 0===i)return("string"===t?String:Number)(e);i=i.call(e,t||"default");if("object"!==_typeof(i))return i;throw new TypeError("@@toPrimitive must return a primitive value.")}!function(h,c,n,a){var o=null,i=function(){function i(e){var t=this;_classCallCheck(this,i),this.debug=!1,this.open=!1,this.allowColumn=!0,this.uniqueId=c.Exo.guid(),this.isSafari=c.Exo.isSafari(),this.supported=this.isSupported(),this.$element=e,this.$field=this.$element.find("select"),this.multiple=!!this.$field.attr("multiple"),this.hasError()&&this.$element.addClass("invalid"),this.$trigger=this.$element.find(".exo-form-select-trigger").attr("id","exo-form-select-trigger-"+this.uniqueId).prop("disabled",this.isDisabled()),this.$wrapper=e.find(".exo-form-select-wrapper"),this.$caret=this.$element.find(".exo-form-select-caret"),this.$label=e.closest(".exo-form-select").find("label").first(),this.placeholder=this.$field.attr("placeholder")||(this.multiple?"Select Multiple":"Select One"),this.$label.attr("id","exo-form-select-label-"+this.uniqueId),this.$trigger.text(this.placeholder),this.$hidden=this.$element.find(".exo-form-select-hidden"),this.supported?(this.$hidden.attr("id","exo-form-select-hidden-"+this.uniqueId).attr("aria-labelledby","exo-form-select-label-"+this.uniqueId+" exo-form-select-trigger-"+this.uniqueId+" exo-form-select-hidden-"+this.uniqueId),this.multiple?this.$hidden.attr("aria-label","Select Option"):this.$hidden.attr("aria-label","Select Options"),this.isDisabled()&&this.$hidden.prop("disabled",!0).attr("tabindex","-1"),this.$field.attr("tabindex")&&this.$hidden.attr("tabindex",this.$field.attr("tabindex")),this.isSafari||this.$field.attr("tabindex","-1"),this.$dropdownWrapper=h("#exo-form-select-dropdown-wrapper"),this.$dropdownWrapper.length||(this.$dropdownWrapper=h('<div id="exo-form-select-dropdown-wrapper" class="exo-form"></div>'),c.Exo.getBodyElement().append(this.$dropdownWrapper)),this.$dropdown=h('<div class="exo-form-select-dropdown exo-form-input '+this.$field.data("drupal-selector")+'" role="combobox" aria-owns="exo-form-select-list-'+this.uniqueId+'" aria-expanded="false"></div>'),this.$dropdownScroll=h('<div class="exo-form-select-scroll"></div>').appendTo(this.$dropdown),this.$dropdownList=h('<ul id="exo-form-select-list-'+this.uniqueId+'" class="exo-form-select-list" role="listbox" aria-labelledby="exo-form-select-label-'+this.uniqueId+'" tabindex="-1"></ul>').appendTo(this.$dropdownScroll),this.$dropdownWrapper.append(this.$dropdown),this.$dropdown.addClass(this.multiple?"is-multiple":"is-single"),this.$field.hasClass("notranslate")&&this.$dropdownList.addClass("notranslate"),!0===this.hasValue()&&this.$element.addClass("filled")):(this.$hidden.remove(),this.$field.show()),this.build(),this.evaluate(),this.bind(),setTimeout(function(){t.$element.addClass("ready")})}return _createClass(i,[{key:"destroy",value:function(){this.unbind(),this.$dropdown.remove(),this.$element.removeData()}},{key:"build",value:function(){var e=this;this.loadOptionsFromSelect(),this.updateTrigger(),this.debug&&(this.$field.show(),setTimeout(function(){e.$trigger.trigger("tap")},500))}},{key:"evaluate",value:function(){var e,t;this.supported&&(t=this.isRequired(),e=this.isDisabled(),this.$field.prop("required",t),this.$trigger.prop("disabled",e),e?this.$element.addClass("form-disabled"):this.$element.removeClass("form-disabled"),this.multiple)&&(t=this.$dropdown.find(".exo-form-checkbox"),e?t.addClass("form-disabled"):t.removeClass("form-disabled"),t.find(".form-checkbox").prop("disabled",e))}},{key:"bind",value:function(){function t(){i.hasValue()?i.$element.addClass("value"):i.$element.removeClass("value")}var i=this;this.$trigger.on("focus.exo.form.select",function(e){i.$trigger.trigger("blur")}).on("click.exo.form.select",function(e){e.preventDefault()}).on("tap.exo.form.select",function(e){i.supported?i.showDropdown():e.preventDefault()}),this.$field.on("state:disabled.exo.form.select",function(e){i.evaluate()}).on("state:required.exo.form.select",function(e){i.evaluate()}).on("state:visible.exo.form.select",function(e){i.evaluate()}).on("state:collapsed.exo.form.select",function(e){i.evaluate()});this.supported?(this.$dropdown.on("tap.exo.form.select",".selector",function(e){i.onItemTap(e)}),this.$dropdown.on("tap.exo.form.select",".close",function(e){i.closeDropdown()}),this.$hidden.on("focusin.exo.form.select",function(e){null!==o&&o.closeDropdown(),i.$wrapper.addClass("focused")}).on("blur.exo.form.select",function(e){i.$wrapper.removeClass("focused")}).on("keydown.exo.form.select",function(e){i.onHiddenKeydown(e)}).on("keyup.exo.form.select",function(e){e.preventDefault()}).on("click.exo.form.select",function(e){e.preventDefault(),i.showDropdown()}),this.$field.on("focus.exo.form.select",function(e){i.isSafari&&i.$hidden.trigger("focus")}).on("change.exo.form.select",function(e){i.loadOptionsFromSelect(),i.updateTrigger()}).on("input.exo.form.select",function(e){t()}),t(),this.$field.attr("autofocus")&&this.showDropdown()):(this.$field.addClass("overlay"),this.$field.on("change.exo.form.select",function(e){i.loadOptionsFromSelect(),i.updateTrigger()}).on("input.exo.form.select",function(e){t()}),t())}},{key:"unbind",value:function(){this.$element.off(".exo.form.select"),this.$dropdown.off(".exo.form.select"),this.$dropdown.find(".search-input").off(".exo.form.select"),this.$field.off(".exo.form.select"),h("body").off(".exo.form.select")}},{key:"onChange",value:function(e){}},{key:"onItemTap",value:function(e){var t,e=h(e.currentTarget),i=e.parent(),o=e.data("option");return this.multiple?(this.$dropdown.find(".selector.selected").removeClass("selected"),(e.is(".active")?(t="remove",e.removeClass("active"),e.find("input").prop("checked",!1)):(t="add",e.addClass("active selected"),e.find("input").prop("checked",!0))).trigger("change"),this.changeSelected(o,t)):(i.find(".active, .selected").removeClass("active selected").removeAttr("aria-selected"),e.addClass("active selected").attr("aria-selected","true"),this.changeSelected(o,"add"),this.closeDropdown(!0))}},{key:"onSearchKeydown",value:function(e){var t,i,o,s;if(this.open)return 9===e.which?((t=this.$dropdown.find(".selector.selected")).length&&(i=t.data("option"),this.changeSelected(i,"add")),i=this.$element.closest("form").find(":input").not(".ignore").not('[tabindex="-1"]'),o=null,s=i.index(this.$hidden),i.each(function(e,t){null===o&&s<e&&h(t).not('[tab-index="-1"]')&&(o=h(t))}),null!==o&&(o.focus(),e.preventDefault()),this.closeDropdown()):27===e.which?this.closeDropdown(!0):(13===e.which&&((t=this.$dropdown.find(".selector.selected")).length&&t.trigger("tap"),e.preventDefault()),40!==e.which&&39!==e.which||(this.highlightOption(this.$dropdown.find(".selector.selected").nextAll(".selector:not(.hide):visible").first(),!0,!0),e.preventDefault()),void(38!==e.which&&37!==e.which||(this.highlightOption(this.$dropdown.find(".selector.selected").prevAll(".selector:not(.hide):visible").first(),!0,!0),e.preventDefault())));e.preventDefault()}},{key:"onSearchKeyup",value:function(e){var i,t;!this.open||!this.isAlphaNumberic(e.which)&&8!==e.which||((i=h(e.currentTarget).val().toString().toLowerCase())?(t=this.$dropdown.find(".selector"),(t=this.multiple?t.filter(":not(.active)"):t).each(function(e,t){0<=h(t).data("option").text.toLowerCase().indexOf(i)?h(t).removeClass("hide"):h(t).addClass("hide")}),this.$dropdown.find(".optgroup").removeClass("hide").each(function(e,t){t=h(t);t.nextUntil(".optgroup").filter(":not(.hide)").length||t.addClass("hide")})):this.$dropdown.find(".hide").removeClass("hide"),this.highlightOption(this.$dropdown.find(".selector:not(.hide):visible").first())),e.preventDefault()}},{key:"isAlphaNumberic",value:function(e){e=String.fromCharCode(e);return/[a-zA-Z0-9-_ ]/.test(e)}},{key:"onHiddenKeydown",value:function(e){var t,i,o;this.open||!0!==e.metaKey&&9!==e.which&&(37!==e.which||this.multiple?39!==e.which||this.multiple?this.isAlphaNumberic(e.which)||38===e.which||40===e.which?38===e.which||40===e.which?(e.which=13,e.preventDefault(),this.showDropdown()):13!==e.which&&(39!==e.which&&37!==e.which&&17!==e.which&&18!==e.which&&32!==e.which&&-1===[9,13,27,37,38,39,40].indexOf(e.which)&&(e.preventDefault(),this.showDropdown(),o=e.which||e.which,o=String.fromCharCode(o).toLowerCase(),this.$dropdown.find(".search-input").val(o),this.onSearchKeyup(e)),e.preventDefault()):e.preventDefault():(t=this.$dropdown.find(".selector.selected").nextAll(".selector:not(.hide):visible").first(),this.highlightOption(t,!0,!0),i=t.data("option"),this.changeSelected(i,"add"),e.preventDefault()):(t=this.$dropdown.find(".selector.selected").prevAll(".selector:not(.hide):visible").first(),this.highlightOption(t,!0,!0),i=t.data("option"),this.changeSelected(i,"add"),e.preventDefault()))}},{key:"populateDropdown",value:function(){for(var e,t=this,i=(this.$dropdownList.find("li").remove(),0===this.$dropdown.find(".search-input").length&&this.$dropdown.prepend('<div class="close notranslate" aria-label="Close">&times;</div>').prepend('<div class="search"><input type="text" class="exo-form-input-item simple search-input" aria-autocomplete="list" aria-controls="exo-form-select-scroll-'+this.uniqueId+'" tabindex="-1"></input></div>').find(".search-input").attr("placeholder",this.placeholder).on("keydown.exo.form.select",function(e){t.onSearchKeydown(e)}).on("keyup.exo.form.select",function(e){t.onSearchKeyup(e)}),this.$dropdown.find(".search-input").attr("placeholder","Search..."),this.getAllOptions()),o=this.$field.data("options-enabled")||[],s=this.$field.data("options-disabled")||[],l=h("<ul />"),n=0;n<i.length;n++){var r=i[n];if(""!==r.value||!0!==this.multiple){var d="exo-form-option-"+this.uniqueId+"-"+n,a="exo-form-option-"+r.text.replace(/\W/g,"-").toLowerCase(),a=h('<li role="option" class="'+a+'" role="listitem" tabindex="-1"></li>');if(r.group)a.addClass("optgroup"),a.html("<span>"+r.text+"</span>");else if(this.multiple){if(!this.isRequired()&&"_none"===r.value)continue;a.addClass("selector exo-form-checkbox ready"),a.html('<span><input id="'+d+'" type="checkbox" class="form-checkbox"><label for="'+d+'" class="option">'+r.text+'<div class="exo-ripple"></div></label></span>')}else a.addClass("selector"),a.html("<span>"+r.text+"</span>");r.selected&&(a.addClass("active").attr("aria-selected","true"),a.find("input").prop("checked",!0)),a.data("option",r),(r.value&&o.length&&!o.includes(r.value)&&"_none"!==r.value&&"_all"!==r.value||r.value&&s.length&&s.includes(r.value)?(a.addClass("disabled"),l):this.$dropdownList).append(a)}}l.children().length&&((e=this.$field.data("options-disabled-label"))&&this.$dropdownList.append(h('<li class="selector-disabled" role="listitem" tabindex="-1"><span>'+e+"</span></li>")),this.$dropdownList.append(l.children())),this.multiple&&this.$dropdownList.find(".form-checkbox").on("change",function(e){t.highlightOption(h(e.currentTarget).closest(".selector"),!1)}),this.highlightOption(),c.attachBehaviors(this.$dropdownList[0])}},{key:"getAllOptions",value:function(e){if(!e)return this.selected;for(var t=[],i=0;i<this.selected.length;i++)t.push(this.selected[i][e]);return t}},{key:"loadOptionsFromSelect",value:function(){var s=this;this.selected=[],this.$field.find("option, optgroup").each(function(e,t){var i=h(t),o={value:"",text:"",selected:!1,group:!1};i.is("optgroup")?(o.text=h(t).attr("label"),o.group=!0):(o.value=i.attr("value"),o.text=i.html(),o.selected=i.is(":selected")),!s.multiple||""!==o.value&&"_none"!==o.value?s.selected.push(o):i.remove(),"-"===o.text.charAt(0)&&(s.allowColumn=!1)})}},{key:"updateTrigger",value:function(){var e=this.getSelectedOptions("value").join("");null===e||""===e||"_none"===e?(this.$trigger.val(""),this.$trigger.attr("placeholder",this.htmlDecode(this.getSelectedOptions("text").join(", ")))):this.$trigger.val(this.htmlDecode(this.getSelectedOptions("text").join(", ")))}},{key:"getSelectedOptions",value:function(e){for(var t=[],i=0;i<this.selected.length;i++)this.selected[i].selected&&t.push(e?this.selected[i][e]:this.selected[i]);return t}},{key:"changeSelect",value:function(e,t){for(var i=!1,o=0;o<this.selected.length;o++)this.multiple||(this.selected[o].selected=!1),this.selected[o].value===e.value&&(i=!0,"add"===t?this.selected[o].selected=!0:"remove"===t&&(this.selected[o].selected=!1));this.updateTrigger(),this.multiple&&this.updateSearch(),this.updateSelect(i?null:e)}},{key:"updateSelect",value:function(e){e&&(e=h("<option></option>").attr("value",e.value).html(e.text),this.$field.append(e)),this.$field.val(this.getSelectedOptions("value")),this.$field[0].dispatchEvent(new Event("change",{bubbles:!0,cancelable:!1})),this.$field[0].dispatchEvent(new Event("input",{bubbles:!0,cancelable:!1}))}},{key:"changeSelected",value:function(e,t){for(var i=!1,o=!1,s=0;s<this.selected.length;s++)this.multiple||(this.selected[s].selected=!1),this.selected[s].value===e.value&&(i=!0,"add"===t?this.selected[s].selected=!0:"remove"===t&&(this.selected[s].selected=!1)),!this.multiple||""===this.selected[s].value&&"_none"===this.selected[s].value||!this.selected[s].selected||(o=!0);if(this.multiple)for(s=0;s<this.selected.length;s++)""!==this.selected[s].value&&"_none"!==this.selected[s].value||(this.selected[s].selected=!o);this.updateTrigger(),this.multiple&&this.updateSearch(),this.updateSelect(i?null:e)}},{key:"updateSearch",value:function(){this.$dropdown.find(".search-input").attr("placeholder",this.getSelectedOptions("text").join(", "))}},{key:"highlightOption",value:function(e,t,i){t=!1!==t,(e=!(e=e||this.$dropdownList.find(".selector.active:eq(0)")).length&&i?this.$dropdownList.find(".selector:eq(0)"):e).length&&(this.$dropdown.find(".selector.selected").removeClass("selected").removeAttr("aria-selected"),e.addClass("selected").attr("aria-selected","true"),this.$dropdown.find(".search-input").attr("aria-activedescendant",e.attr("id")),this.$hidden.attr("aria-activedescendant",e.attr("id")),t)&&this.highlightScrollTo(e)}},{key:"highlightScrollTo",value:function(e){var t,i,o;(e=e||this.$dropdown.find(".selector.selected")).length&&(t=this.$dropdownScroll.scrollTop(),i=this.$dropdownScroll.outerHeight(),o=this.$dropdownScroll.offset().top,i+t<(o=t+e.offset().top-o)+(e=e.outerHeight())?this.$dropdownScroll.scrollTop(o+e-i):o<t&&this.$dropdownScroll.scrollTop(o))}},{key:"hasValue",value:function(){var e=this.$field.val();return e&&"object"===_typeof(e)?(1!==e.length||""!==e[0])&&0<e.length:""!==e&&"- Any -"!==e&&"_none"!==e}},{key:"hasError",value:function(){return this.$field.hasClass("error")}},{key:"showDropdown",value:function(){var e=this;if(h("body").trigger("tap"),this.open)return this.closeDropdown();this.populateDropdown(),this.open=!0,(o=this).$dropdownList.css("max-height",""),this.positionDropdown(),c.Exo.lockOverflow(this.$dropdown),c.Exo.showShadow({opacity:.2}),this.$element.addClass("active"),this.$wrapper.addClass("focused"),this.$dropdown.addClass("active").find(".search-input").focus(),this.$dropdownWrapper.attr("class",this.$element.closest("form").attr("class")).addClass("exo-form-select-dropdown-wrapper").css({padding:0,margin:0}),this.$dropdownScroll.scrollTop(0),this.highlightScrollTo(),setTimeout(function(){e.$element.addClass("animate"),e.$dropdown.addClass("animate").attr("aria-expanded","true")},50),this.windowHideDropdown()}},{key:"windowHideDropdown",value:function(){var t=this;h("body").on("tap."+this.uniqueId,function(e){!t.open||h(e.target).closest(t.$dropdown).length||t.closeDropdown()})}},{key:"positionDropdown",value:function(){if(!0===this.open){var e=c.Exo.$window.scrollTop(),t=c.Exo.$window.height(),i=e+t,o=this.$wrapper.offset().top,s=this.$dropdown.outerHeight(),l=o+s,n=(h(".exo-fixed-header .exo-fixed-element").outerHeight()||0)+a.offsets.top,r=(this.$dropdown.removeClass("from-top from-bottom").css({left:this.$trigger.offset().left,width:this.$trigger.outerWidth()}),"top");switch(t-n<s?(s=t-n-this.$dropdown.find(".search-input").height()-20,this.$dropdownList.css("max-height",s),c.Exo.$window.scrollTop(o-n-10)):l<i||(e+n<o-s?r="bottom":c.Exo.$window.scrollTop(o-n-(i-(e+n)-s)/2)),r){case"top":this.$dropdown.addClass("from-top").css("top",this.$trigger.offset().top);break;case"bottom":var d=c.Exo.$document.height()-(this.$trigger.offset().top+this.$trigger.outerHeight());this.$dropdown.addClass("from-bottom").css("bottom",d)}this.multiple&&this.allowColumn&&this.$dropdownScroll[0].scrollHeight>2*this.$dropdownScroll.height()&&(t=this.$dropdownScroll.outerWidth(),o=(l=this.$dropdownScroll.offset().left)+t,l+(i=780)<c.Exo.$window.width()?(this.$dropdownList.addClass("column--3"),this.$dropdownScroll.css({marginRight:-1*(i-t),width:i,minWidth:i,maxWidth:i})):0<o-i&&(this.$dropdownList.addClass("column--3"),this.$dropdownScroll.css({marginLeft:-1*(i-t),width:i,minWidth:i,maxWidth:i})))}}},{key:"closeDropdown",value:function(e){var t=this;!0===this.open&&(this.open=!1,o=null,this.$dropdown.attr("aria-expanded","false"),this.$dropdown.removeClass("animate").find(".search-input").val(""),this.$element.removeClass("animate"),this.$wrapper.removeClass("focused"),this.updateSearch(),c.Exo.hideShadow(),h("body").off("."+this.uniqueId),setTimeout(function(){t.$dropdownList.find(".hide").removeClass("hide"),c.Exo.unlockOverflow(t.$dropdown),!0===t.hasValue()?t.$element.addClass("filled"):t.$element.removeClass("filled"),!1===t.open&&(t.$element.removeClass("active"),t.$dropdown.removeClass("active").removeAttr("style"),t.$dropdownWrapper.removeAttr("class"))},350),e)&&setTimeout(function(){t.$hidden.trigger("focus",[1])})}},{key:"htmlDecode",value:function(e){return h("<div/>").html(e).text().replace(/&amp;/g,"&")}},{key:"isRequired",value:function(){return void 0!==this.$field.attr("required")}},{key:"isDisabled",value:function(){return this.$field.is(":disabled")}},{key:"isSupported",value:function(){return!0===c.Exo.isIE()?8<=n.documentMode:!c.Exo.isTouch()}}]),i}();c.behaviors.exoFormSelect={instances:{},once:!1,attach:function(e){h(e).find(".form-item.exo-form-select-js").once("exo.form.select").each(function(e,t){t=new i(h(t));c.behaviors.exoFormSelect.instances[t.uniqueId]=t}),!1===this.once&&(this.once,c.Exo.addOnResize("exo.form",function(){for(var e in c.behaviors.exoFormSelect.instances)c.behaviors.exoFormSelect.instances.hasOwnProperty(e)&&c.behaviors.exoFormSelect.instances[e].positionDropdown()}))},detach:function(e,t,i){if("unload"===i&&e!==n){var o,s,l=h(e).find(".form-item.exo-form-select-js");for(o in c.behaviors.exoFormSelect.instances)c.behaviors.exoFormSelect.instances.hasOwnProperty(o)&&(s=c.behaviors.exoFormSelect.instances[o]).$element.is(l)&&(s.destroy(),delete c.behaviors.exoFormSelect.instances[o])}}}}(jQuery,Drupal,document,Drupal.displace);