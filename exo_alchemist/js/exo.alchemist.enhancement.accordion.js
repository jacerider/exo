"use strict";function _classCallCheck(e,i){if(!(e instanceof i))throw new TypeError("Cannot call a class as a function")}function _defineProperties(e,i){for(var t=0;t<i.length;t++){var o=i[t];o.enumerable=o.enumerable||!1,o.configurable=!0,"value"in o&&(o.writable=!0),Object.defineProperty(e,o.key,o)}}function _createClass(e,i,t){return i&&_defineProperties(e.prototype,i),t&&_defineProperties(e,t),e}!function(s,l){var o=function(){function n(e,i){var r=this;_classCallCheck(this,n),this.id="",this.idSelector="",this.speed=5e3,this.history=!1,this.$wrapper=i,this.id=e,this.idSelector='data-ee--accordion-id="'+this.id+'"',this.$items=i.find(".ee--accordion-item["+this.idSelector+"]"),this.$triggers=i.find(".ee--accordion-trigger["+this.idSelector+"]"),this.$contents=i.find(".ee--accordion-content["+this.idSelector+"]"),this.history=void 0!==i.data("ee--accordion-history");var t=void 0!==i.data("ee--accordion-collapse");this.$contents.hide();this.$items.each(function(e,i){var t=s(i),o=t.find(".ee--accordion-trigger"),n=t.find(".ee--accordion-content");if(!o.data("ee--accordion-item-id")){var a=r.id+"-trigger-"+e,c=r.id+"-content-"+e;o.attr("id",a).attr("data-ee--accordion-item-id",e).attr("aria-controls",c),n.attr("id",c).attr("aria-labelledby",a)}});var o=this.$triggers.first();l.Exo.$window.on("popstate.exo.alchemist.enhancement.tabs."+this.id,function(e){var i=l.ExoAlchemistEnhancement.getHashForKey("ee--accordion");if(i&&void 0!==i[r.id]){var t=r.$triggers.filter('[data-ee--accordion-item-id="'+i[r.id]+'"]');t.length&&r.show(t.first(),!0,!0,!1)}else r.show(r.$triggers.first(),!0,!0,!1)}),this.isLayoutBuilder()?(l.ExoAlchemistAdmin.lockNestedFields(this.$items),l.Exo.$document.on("exoComponentFieldEditActive.exo.alchemist.enhancement.accordion."+this.id,function(e,i){var t=s(i);t.hasClass("ee--accordion-item")&&r.$wrapper.find(t).length&&(r.show(t,!1,!1,r.history),l.ExoAlchemistAdmin.sizeFieldOverlay(t),l.ExoAlchemistAdmin.sizeTarget(t))})):function(){var e=l.ExoAlchemistEnhancement.getHashForKey("ee--accordion");if(e&&void 0!==e[r.id]){var i=r.$triggers.filter('[data-ee--accordion-item-id="'+e[r.id]+'"]');i.length&&(o=i.first())}}(),this.$triggers.on("click.exo.alchemist.enhancement.accordion",function(e){e.preventDefault(),r.show(s(e.currentTarget),!0,r.isLayoutBuilder(),r.history)}).on("keydown.exo.alchemist.enhancement.accordion",function(e){var i;switch(e.which){case 13:case 32:e.preventDefault(),e.stopPropagation(),r.show(s(e.currentTarget),!0,r.isLayoutBuilder(),r.history);break;case 40:e.preventDefault(),e.stopPropagation(),(i=s(e.currentTarget).closest(".ee--accordion-item["+r.idSelector+"]").next().find(".ee--accordion-trigger["+r.idSelector+"]")).length&&(r.show(i,!0,r.isLayoutBuilder(),r.history),i.focus());break;case 38:e.preventDefault(),e.stopPropagation(),(i=s(e.currentTarget).closest(".ee--accordion-item["+r.idSelector+"]").prev().find(".ee--accordion-trigger["+r.idSelector+"]")).length&&(r.show(i,!0,r.isLayoutBuilder(),r.history),i.focus())}}),!1==t&&(l.Exo.$window.on("ee--tab.open."+this.id,function(e,i){i.content.find(r.$wrapper).length&&r.show(o,!0,!0,!1)}),this.show(o,!1,!0,!1))}return _createClass(n,[{key:"show",value:function(e,i,t,o){var n=this;i=void 0===i||i,t=void 0!==t&&t,o=void 0===o||o;var a=e.closest(".ee--accordion-item["+this.idSelector+"]"),c=a.find(".ee--accordion-content["+this.idSelector+"]"),r=e.data("ee--accordion-item-id");if(c.length){var s=a.hasClass("show"),d=this.$items.filter(".show"),h=d.find(".ee--accordion-content["+this.idSelector+"]");if(this.isLayoutBuilder()){if(s)return;l.ExoAlchemistAdmin.lockNestedFields(d)}(!s||t)&&s||(d.removeClass("show"),e.attr("aria-expanded","false"),i?h.slideToggle(350,"swing"):h.hide()),s&&!t||!o||void 0===r||l.ExoAlchemistEnhancement.setHashForKey("ee--accordion",r,this.id),s||(a.addClass("show"),e.attr("aria-expanded","true"),i?c.slideToggle(350,"swing",function(){l.Exo.checkElementPosition(),n.isLayoutBuilder()&&l.ExoAlchemistAdmin.unlockNestedFields(a)}):(c.show(),l.Exo.checkElementPosition(),this.isLayoutBuilder()&&l.ExoAlchemistAdmin.unlockNestedFields(a)))}}},{key:"unload",value:function(){l.Exo.$document.off("exoComponentFieldEditActive.exo.alchemist.enhancement.accordion."+this.id),l.Exo.$window.off("popstate.exo.alchemist.enhancement.accordion."+this.id),l.Exo.$window.off("ee--tab.open."+this.id)}},{key:"isLayoutBuilder",value:function(){return l.ExoAlchemistAdmin&&l.ExoAlchemistAdmin.isLayoutBuilder()}}]),n}();l.behaviors.exoAlchemistEnhancementAccordion={count:0,instances:{},attach:function(e){var t=this;s(".ee--accordion-wrapper",e).once("exo.alchemist.enhancement").each(function(){var e=s(this),i=e.data("ee--accordion-id");e.data("ee--accordion-count",t.count),t.instances[i+t.count]=new o(i,e),t.count++})},detach:function(e,i,t){if("unload"===t){var o=this;s(".ee--accordion-wrapper",e).each(function(){var e=s(this),i=e.data("ee--accordion-id")+e.data("ee--accordion-count");void 0!==o.instances[i]&&(o.instances[i].unload(),delete o.instances[i])})}}}}(jQuery,Drupal,drupalSettings);