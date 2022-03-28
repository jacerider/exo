"use strict";function _classCallCheck(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}function _defineProperties(e,t){for(var i=0;i<t.length;i++){var o=t[i];o.enumerable=o.enumerable||!1,o.configurable=!0,"value"in o&&(o.writable=!0),Object.defineProperty(e,o.key,o)}}function _createClass(e,t,i){return t&&_defineProperties(e.prototype,t),i&&_defineProperties(e,i),e}!function(p,e,v,u,m){var t=function(){function e(){_classCallCheck(this,e),this.label="ExoAlchemistAdmin",this.doDebug=!1,this.ranOnce=!1,this.overlapOffset=10,this.$activeTarget=null,this.$activeComponent=null,this.$activeField=null,this.watchFinished=null}return _createClass(e,[{key:"setup",value:function(){var t=this;this.buildElements(),v.Exo.$document.on("drupalViewportOffsetChange",function(e){t.$activeTarget&&!t.$activeComponent&&t.sizeTarget(t.$activeTarget)}),v.Exo.addOnResize("exo.alchemist",function(e){t.$activeComponent&&t.sizeComponentOverlay(t.$activeComponent),t.$activeField&&t.sizeFieldOverlay(t.$activeField),t.$activeTarget&&t.sizeTarget(t.$activeTarget)}),document.addEventListener("aos:finish",function(e){t.watch()})}},{key:"buildElements",value:function(){this.$obtrusiveElements=p(".exo-fixed-region, .exo-layout-builder-top").addClass("exo-component-hide"),this.$shade=p('<div class="exo-alchemist-shade" />').appendTo(v.Exo.$exoContent),this.$highlight=p('<div class="exo-alchemist-highlight" />').appendTo(v.Exo.$exoContent),this.$overlay=p('<div class="exo-alchemist-overlay exo-reset exo-font" />').appendTo(v.Exo.$exoContent),this.$overlayHeader=p('<div class="exo-alchemist-overlay-header" />').appendTo(this.$overlay),this.$overlayOps=p("<span />").appendTo(this.$overlayHeader).wrap('<div class="exo-alchemist-ops exo-alchemist-overlay-ops" />'),this.$overlayClose=p('<div class="exo-alchemist-overlay-close" />').appendTo(this.$overlayHeader),this.$overlayClose.html("<a>"+u.exoAlchemist.icons.close+"</a>"),this.$target=p('<div class="exo-alchemist-target exo-reset exo-font" />').appendTo(v.Exo.$exoContent),this.$targetHeader=p('<div class="exo-alchemist-target-header" />').appendTo(this.$target),this.$targetTitle=p('<div class="exo-alchemist-target-title" />').appendTo(this.$target),this.$targetOps=p("<span />").appendTo(this.$targetHeader).wrap('<div class="exo-alchemist-ops exo-alchemist-target-ops" />'),this.$targetClose=p('<div class="exo-alchemist-target-close" />').appendTo(this.$targetHeader),this.$targetClose.html("<a>"+u.exoAlchemist.icons.close+"</a>"),this.$fieldBreadcrumbs=p('<ul class="exo-alchemist-breadcrumbs" />').appendTo(this.$overlay)}},{key:"attach",value:function(i){var n=this;return!1===this.ranOnce&&(this.ranOnce=!0,this.setup()),new Promise(function(e,t){function o(){p("#layout-builder").trigger("exo.alchemist.ready")}p("#layout-builder").once("exo.alchemist").each(function(e,t){if(n.$activeComponent&&!p(t).find(n.$activeComponent).length){var i=p("#"+n.$activeComponent.attr("id"));i.length?p(t).imagesLoaded(function(){if(n.setComponentActive(i,!0),n.$activeField&&!p(i).find(n.$activeField).length){var e=p("#"+n.$activeField.attr("id"));e.length&&n.setFieldActive(e,!0)}o()}):o()}else o()}),p(".exo-component-edit",i).once("exo.alchemist.component").each(function(e,t){var i=p(t),o=p(".exo-component-field-edit, .exo-section",i).length;i.hasClass("exo-component-locked")&&!o&&i.addClass("exo-component-blocked")}).on("click.exo.alchemist.component",function(e){var t=p(e.currentTarget);e.preventDefault(),e.stopPropagation(),t.hasClass("exo-component-blocked")||(n.setComponentActive(t,n.isNestedComponent(e.currentTarget)),p(".exo-component-field-edit:hover").trigger("mouseenter"))}).on("mouseenter.exo.alchemist.component",function(i){function e(){var e=p(i.currentTarget),t=JSON.parse(i.currentTarget.getAttribute("data-exo-component"));e.hasClass("exo-component-blocked")?n.showTarget(e,"This component cannot be changed.","regular-lock"):n.showTarget(e,"Click to focus this <strong>"+t.label.toLowerCase()+"</strong> component","regular-cog")}n.$activeComponent?n.isNestedComponent(i.currentTarget)&&e():e()}).on("mouseleave.exo.alchemist.component",function(e){n.$activeComponent?n.isNestedComponent(e.currentTarget)&&n.hideTarget():n.hideTarget()}),p(".exo-component-event-allow",i).once("exo.alchemist.allow").on("click.exo.alchemist.allow",function(e){if(!p(e.currentTarget).hasClass("exo-component-field-edit")){var t=p(e.currentTarget).closest(".exo-component-field-edit");t.length&&t.trigger("click")}n.watch()}),p(".exo-component-field-edit",i).once("exo.alchemist.field").on("click.exo.alchemist.field",function(e){var t=p(e.currentTarget);t.hasClass("exo-component-field-edit-lock")||t.closest(".exo-component-field-edit-lock").length||n.setFieldActive(t)}).on("mouseenter.exo.alchemist.field",function(e){if(!n.$activeField){var t=p(e.currentTarget);if(t.hasClass("exo-component-field-edit-lock")||t.closest(".exo-component-field-edit-lock").length)return;var i=JSON.parse(e.currentTarget.getAttribute("data-exo-field"));n.showTarget(t,"Click to manage <strong>"+i.label.toLowerCase()+"</strong> field","regular-cog")}}).on("mouseleave.exo.alchemist.field",function(e){n.$activeField||n.hideTarget()}),e(!0)})}},{key:"watch",value:function(t,v){var u=this,m=[];m.push([this.$activeComponent,"sizeComponentOverlay"]),m.push([this.$activeField,"sizeFieldOverlay"]),m.push([this.$activeTarget,"sizeTarget"]),null===this.watchFinished&&(v=v||10,this.watchFinished=0,m.forEach(function(e){var s=e[0];if(s&&s.length){var a=e[1],l=0,r=!0===t,c=Math.round(s.outerHeight(!0)),h=Math.round(s.outerWidth(!0)),d=Math.round(s.offset().top),p=Math.round(s.offset().left);!function n(){setTimeout(function(){var e=Math.round(s.outerHeight(!0)),t=Math.round(s.outerWidth(!0)),i=Math.round(s.offset().top),o=Math.round(s.offset().left);e!==c?(c=e,r=!0,l=0):t!==h?(h=t,r=!0,l=0):i!==d?(d=i,r=!0,l=0):o!==p?(p=o,r=!0,l=0):l++,v<=l?(u.watchFinished++,!0===r&&u[a](s),u.watchFinished===m.length&&(u.watchFinished=null)):n()},10)}()}else u.watchFinished++;u.watchFinished===m.length&&setTimeout(function(){u.watchFinished=null},15)}))}},{key:"isLayoutBuilder",value:function(){return u.exoAlchemist&&u.exoAlchemist.isLayoutBuilder}},{key:"setComponentActive",value:function(e,t){var i=this;this.$activeComponent&&!t||((this.$activeComponent=e).addClass("exo-component-edit-active"),this.$obtrusiveElements.length&&this.$obtrusiveElements.addClass("exo-component-hide-active"),this.buildComponentOps(e),this.hideTarget(),this.showComponentOverlay(e,function(){i.setComponentInactive()}),p(document).trigger("exoComponentActive",this.$activeComponent))}},{key:"buildComponentOps",value:function(e){var t=u.exoAlchemist.componentOps,i=e.data("exo-component"),o=p.extend({},i);this.$overlayOps.html(""),this.showComponentOps(),i.description&&p('<div class="exo-description exo-component-description">'+i.description+"</div>").appendTo(this.$overlayOps);var n=e.find("[data-exo-component-ops]").data("exo-component-ops");if(n)for(var s in i.ops.concat(Object.keys(n)),n)n.hasOwnProperty(s)&&(i.ops.push(s),t[s]=n[s]);for(var a in t)if(t.hasOwnProperty(a)&&i.ops.includes(a)){var l=t[a],r=l.url;for(var c in o)o.hasOwnProperty(c)&&("string"!=typeof o[c]&&"number"!=typeof o[c]||(r=r.replace(new RegExp("-"+c+"-","g"),o[c])));var h=l.title;void 0!==i[a+"_badge"]&&(h+=' <span class="exo-alchemist-op-badge">'+i[a+"_badge"]+"</span>"),r=r.replace(new RegExp("(/-.*?)-","g"),"");var d=p('<a href="'+r+'" title="'+l.label+'" class="exo-component-op exo-field-op-'+a+'">'+h+"</a>").appendTo(this.$overlayOps);r&&d.addClass("use-ajax"),d.data("dialog-type","dialog"),d.data("dialog-renderer","off_canvas"),d.data("dialog-options",{exo_modal:{title:l.label,subtitle:l.description,icon:l.icon}})}p(document).trigger("exoComponentOps",this.$overlayOps),v.attachBehaviors(this.$overlayOps[0])}},{key:"showComponentOps",value:function(){this.$overlayOps.addClass("active")}},{key:"hideComponentOps",value:function(){this.$overlayOps.removeClass("active")}},{key:"isNestedComponent",value:function(e){return null!==this.$activeComponent&&0!==this.$activeComponent.find(e).length}},{key:"setComponentInactive",value:function(){if(this.$activeComponent){var e=this.$activeComponent.parent().closest(".exo-component-edit");if(this.setFieldInactive(),this.$activeComponent.removeClass("exo-component-edit-active"),p(document).trigger("exoComponentInactive",this.$activeComponent),this.$activeComponent=null,e.length)return void this.setComponentActive(e);this.hideTarget(),this.hideComponentOverlay(),this.hideComponentOps(),this.$obtrusiveElements.length&&this.$obtrusiveElements.removeClass("exo-component-hide-active")}}},{key:"showComponentOverlay",value:function(i,o){var n=this;return new Promise(function(e,t){n.restrictComponentOverlayPointerEvents(),n.sizeComponentOverlay(i),o&&(n.showOverlayClose(function(e){n.hideOverlayClose(),o()}),n.$shade.off("click").on("click.exo",function(e){e.target===n.$shade.get(0)&&o()})),e(!0)})}},{key:"showComponentClose",value:function(){this.$overlayClose.addClass("active")}},{key:"hideComponentClose",value:function(){this.$overlayClose.removeClass("active")}},{key:"hideComponentOverlay",value:function(){var i=this;return new Promise(function(t,e){i.hideComponentClose(),i.$shade.off("click.exo"),i.$overlay.one(v.Exo.transitionEvent,function(e){i.$shade.removeAttr("style"),i.$overlay.removeAttr("style"),t(!0)}),i.$shade.css({opacity:0,visibility:"hidden"}),i.$overlay.css({opacity:0,visibility:"hidden"}),t(!0)})}},{key:"sizeComponentOverlay",value:function(e){var t=this,i=e.outerWidth(!0),o=e.outerHeight(!0),n=e.offset(),s=n.top-m.offsets.top,a=parseInt(e.css("marginTop").replace("px",""));0<a?s-=a:(s+=a,o-=2*a);var l=s+o,r=n.left-m.offsets.left-e.css("marginLeft").replace("px",""),c=r+i;this.$shade.css({top:"0px",right:"0px",bottom:"0px",left:"0px",width:"100%",height:"100%",visibility:"visible",clipPath:"polygon(0% 0%, 0% 100%, "+r+"px 100%, "+r+"px "+s+"px, "+c+"px "+s+"px, "+c+"px "+l+"px, "+r+"px "+l+"px, "+r+"px 100%, 100% 100%, 100% 0%)"}),setTimeout(function(){t.$shade.css("opacity",1)}),this.$overlay.css({top:s+"px",left:r+"px",width:i+"px",height:o+"px",opacity:1,visibility:"visible"})}},{key:"allowComponentOverlayPointerEvents",value:function(){this.$shade.removeClass("restrict")}},{key:"restrictComponentOverlayPointerEvents",value:function(){this.$shade.addClass("restrict")}},{key:"showTarget",value:function(e,t,i){clearTimeout(this.targetTimer),this.$target.off(v.Exo.transitionEvent),this.$activeTarget=e,i&&(t='<i class="exo-icon exo-icon-font icon-'+i+'" aria-hidden="true"></i> '+t),t=t?"<span>"+t+"</span>":"",this.$target.off(v.Exo.transitionEvent),t?(this.$targetTitle.addClass("active"),this.$targetTitle.html(t).show()):this.$targetTitle.removeClass("active"),this.sizeTarget(e)}},{key:"hideTarget",value:function(){var t=this;clearTimeout(this.targetTimer),this.targetTimer=setTimeout(function(){t.hideTargetClose(),t.$target.one(v.Exo.transitionEvent,function(e){t.$target.removeAttr("style")}),t.$activeTarget=null,t.$target.css({opacity:0,visibility:"hidden"})},200)}},{key:"showTargetClose",value:function(t){this.$targetClose.addClass("active"),t&&this.$targetClose.off("click").on("click.exo",function(e){e.preventDefault(),e.stopPropagation(),t(e)})}},{key:"hideTargetClose",value:function(){this.$targetClose.removeClass("active"),this.$targetClose.off("click.exo")}},{key:"allowTargetPointerEvents",value:function(){this.$target.removeClass("restrict")}},{key:"restrictTargetPointerEvents",value:function(){this.$target.addClass("restrict")}},{key:"sizeTarget",value:function(e){var t=v.Exo.$window.outerWidth(),i=e.offset(),o=e.outerWidth(!0),n=e.outerHeight(!0),s=i.top-m.offsets.top,a=parseInt(e.css("marginTop").replace("px",""));0<a?s-=a:(s+=a,n-=2*a);var l=i.left-m.offsets.left-e.css("marginLeft").replace("px","");if(this.$activeComponent){var r=this.$activeComponent.offset(),c=parseInt(this.$activeComponent.css("marginTop").replace("px",""));r.top-c>=i.top&&(s+=this.overlapOffset,l+=this.overlapOffset,o-=2*this.overlapOffset,n-=2*this.overlapOffset)}this.$target.css({top:s+"px",left:l+"px",width:o+"px",height:n+"px",opacity:1,visibility:"visible"}),this.$target.attr("data-align",t/2<l?"right":"left")}},{key:"lockNestedFields",value:function(e){e.addClass("exo-component-field-edit-lock")}},{key:"unlockNestedFields",value:function(e){e.removeClass("exo-component-field-edit-lock"),p(".exo-component-field-edit:hover").trigger("mouseenter")}},{key:"setFieldActive",value:function(e,t){var i=this;this.$activeField&&!t||((this.$activeField=e).addClass("exo-component-field-edit-active"),this.restrictTargetPointerEvents(),this.buildFieldOps(e),this.buildBreadcrumbs(),this.sizeComponentOverlay(this.$activeComponent),this.showFieldOverlay(e,function(){i.setFieldInactive()}),p(document).trigger("exoComponentFieldEditActive",this.$activeField))}},{key:"buildFieldOps",value:function(e){var t=p.extend({},u.exoAlchemist.fieldOps),i=e.closest(".exo-component-edit").data("exo-component"),o=e.data("exo-field"),n=p.extend({},i,o);this.$targetOps.html(""),this.showFieldOps(),this.$targetOps.addClass("active"),o.description&&p('<div class="exo-description exo-field-description">'+o.description+"</div>").appendTo(this.$targetOps);var s=e.closest("[data-exo-component-field-ops]").data("exo-component-field-ops");if(s)for(var a in o.ops.concat(Object.keys(s)),s)s.hasOwnProperty(a)&&(o.ops.push(a),t[a]=s[a]);for(var l in t)if(t.hasOwnProperty(l)&&o.ops.includes(l)){var r=t[l],c=r.url;for(var h in n)n.hasOwnProperty(h)&&("string"!=typeof n[h]&&"number"!=typeof n[h]||(c=c.replace("-"+h+"-",n[h])));var d=p('<a href="'+c+'" title="'+r.label+'" class="exo-field-op exo-field-op-'+l+'">'+r.title+"</a>").appendTo(this.$targetOps);c&&d.addClass("use-ajax"),d.data("dialog-type","dialog"),d.data("dialog-renderer","off_canvas"),d.data("dialog-options",{exo_modal:{title:r.label,subtitle:r.description,icon:r.icon}})}p(document).trigger("exoComponentFieldOps",this.$targetOps),v.attachBehaviors(this.$targetOps[0])}},{key:"showFieldOps",value:function(){this.hideComponentOps(),this.hideComponentClose(),this.$targetOps.addClass("active")}},{key:"hideFieldOps",value:function(){this.showComponentOps(),this.showComponentClose(),this.$targetOps.removeClass("active")}},{key:"setFieldInactive",value:function(){this.$activeField&&(p(document).trigger("exoComponentFieldEditInactive",this.$activeField),this.$activeField.removeClass("exo-component-field-edit-active"),this.$activeField=null,this.hideFieldOverlay(),this.hideFieldOps(),this.hideBreadcrumbs(),this.hideTarget(),this.allowTargetPointerEvents())}},{key:"showFieldOverlay",value:function(i,o){var n=this;return new Promise(function(e,t){n.$highlight.off(v.Exo.transitionEvent),n.showTarget(i),n.restrictFieldOverlayPointerEvents(),n.sizeFieldOverlay(i),o&&(n.showTargetClose(function(e){n.hideTargetClose(),o()}),n.$highlight.off("click").on("click.exo",function(e){e.preventDefault(),e.stopPropagation(),e.target===n.$highlight.get(0)&&o()})),e(!0)})}},{key:"allowFieldOverlayPointerEvents",value:function(){this.$highlight.removeClass("restrict")}},{key:"restrictFieldOverlayPointerEvents",value:function(){this.$highlight.addClass("restrict")}},{key:"hideFieldOverlay",value:function(){var i=this;return new Promise(function(t,e){i.hideTargetClose(),i.$highlight.off("click.exo"),i.$highlight.one(v.Exo.transitionEvent,function(e){i.$highlight.removeAttr("style"),t(!0)}),i.$highlight.css({opacity:0,visibility:"hidden"}),t(!0)})}},{key:"sizeFieldOverlay",value:function(e){var t=this,i=this.$activeComponent.offset(),o=this.$activeComponent.outerWidth(!0),n=this.$activeComponent.outerHeight(!0),s=i.top,a=parseInt(this.$activeComponent.css("marginTop").replace("px",""));0<a?s-=a:(s+=a,n-=2*a);var l=i.left-parseInt(this.$activeComponent.css("marginLeft").replace("px","")),r=e.outerWidth(!0),c=e.outerHeight(!0),h=e.offset(),d=h.top-s-e.css("marginTop").replace("px",""),p=d+c,v=h.left-l-e.css("marginLeft").replace("px",""),u=v+r;this.$highlight.css({top:s-m.offsets.top+"px",bottom:s+n+"px",left:l-m.offsets.left+"px",right:l+o+"px",width:o,height:n,visibility:"visible",clipPath:"polygon(0% 0%, 0% 100%, "+v+"px 100%, "+v+"px "+d+"px, "+u+"px "+d+"px, "+u+"px "+p+"px, "+v+"px "+p+"px, "+v+"px 100%, 100% 100%, 100% 0%)"}),setTimeout(function(){t.$highlight.css("opacity",1)},10)}},{key:"showOverlayClose",value:function(t){this.$overlayClose.addClass("active"),t&&this.$overlayClose.off("click.exo").on("click.exo",function(e){e.preventDefault(),e.stopPropagation(),t(e)})}},{key:"hideOverlayClose",value:function(){this.$overlayClose.removeClass("active"),this.$overlayClose.off("click.exo")}},{key:"buildBreadcrumbs",value:function(){var n=this;if(this.hideBreadcrumbs(),this.$activeField){var e=this.$activeField,t=this.$activeComponent.find(".exo-component-field-edit"),i=(t=(t=(t=t.not(e.find(".exo-component-field-edit"))).not(e.parents(".exo-component-group").find(".exo-component-field-edit"))).add(e.parents(".exo-component-field-edit"))).overlaps(e).add(e);p('<li class="exo-alchemist-breadcrumb-label">Nested Elements:</li>').appendTo(this.$fieldBreadcrumbs),i.each(function(e,t){var i=p(t),o=i.data("exo-field");p('<li class="exo-alchemist-breadcrumb-field"><a>'+o.label+"</a></li>").on("click",function(e){e.preventDefault(),e.stopPropagation(),n.setFieldInactive(),n.setFieldActive(i,!0)}).appendTo(n.$fieldBreadcrumbs)})}}},{key:"hideBreadcrumbs",value:function(){this.$fieldBreadcrumbs.children().remove()}},{key:"getActiveComponent",value:function(){return this.$activeComponent}},{key:"getActiveField",value:function(){return this.$activeField}}]),e}();v.ExoAlchemistAdmin=v.ExoAlchemistAdmin?v.ExoAlchemistAdmin:new t,v.behaviors.exoAlchemistAdmin={attach:function(e){v.ExoAlchemistAdmin.attach(e)}},v.AjaxCommands.prototype.exoComponentFocus=function(e,t,i){var o=p("#"+t.id);o.length&&p("#layout-builder").on("exo.alchemist.ready",function(e){v.ExoAlchemistAdmin.setComponentActive(o,!0)})},v.AjaxCommands.prototype.exoComponentBlur=function(e,t,i){v.ExoAlchemistAdmin.setComponentInactive()},v.AjaxCommands.prototype.exoComponentFieldFocus=function(e,t,i){var o=p("#"+t.id);o.length&&p("#layout-builder").on("exo.alchemist.ready",function(e){v.ExoAlchemistAdmin.setFieldActive(o,!0)})},v.AjaxCommands.prototype.exoComponentFieldBlur=function(e,t,i){v.ExoAlchemistAdmin.setFieldInactive()}}(jQuery,_,Drupal,drupalSettings,Drupal.displace);