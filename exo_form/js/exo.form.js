"use strict";!function(a,n){var i;n.behaviors.exoForm={once:!1,attach:function(e){i=a("form.exo-form:visible"),a(".exo-form").once("exo.form.init").each(function(e,o){o=a(o);a("> *:visible",o).length||o.append("<div></div>"),o.removeClass("is-disabled"),o.find(".container-inline").removeClass("container-inline"),o.find(".form--inline").removeClass("form--inline").addClass("exo-form-inline"),o.find(".form-items-inline").removeClass("form-items-inline")}),a(".exo-form.is-disabled").each(function(e,o){a(o).removeClass("is-disabled")}),a(".exo-form-button-disabled-clone").each(function(e,o){a(o).remove()}),a(".exo-form-button-displayed-has-clone").each(function(e,o){a(o).removeClass("exo-form-button-displayed-has-clone").show()});function t(e){var o=(e=a(e.target)).closest("form.exo-form"),t=e.data("exo-form-button-disable-message"),n=e.clone().css({minWidth:e.outerWidth()+"px",textAlign:"center"}).addClass("exo-form-button-disabled-clone is-disabled").insertAfter(e);t&&n.text(t),e.addClass("exo-form-button-displayed-has-clone").hide(),e.data("exo-form-button-disable-form")&&setTimeout(function(){o.addClass("is-disabled")},100)}var o;a(".exo-form-button-disable-on-click",e).once("exo.form.disable").on("mousedown",function(e){var o=a(e.target);setTimeout(function(){o.hasClass("exo-form-button-displayed-has-clone")||t(e)},100)}).on("click",t),a(e).find("td .dropbutton-wrapper").once("exo.form.td.compact").each(function(e,o){setTimeout(function(){a(o).css("min-width",a(o).outerWidth())})}).parent().addClass("exo-form-table-compact"),a(e).find("td.views-field-changed, td.views-field-created").once("exo.form.td.compact").addClass("exo-form-table-compact"),a(e).find("td > .exo-icon").once("exo.form.td.compact").each(function(e,o){o=a(o).parent();1===o.children(":not(.exo-icon-label)").length&&o.addClass("exo-form-table-compact")}),a(e).find("table").once("exo.form.table").each(function(e,o){o=a(o);o.closest("form.exo-form").length||o.addClass("exo-form-table-wrap"),o.outerWidth()>o.parent().outerWidth()+2&&o.wrap('<div class="exo-form-table-overflow" />')}),a(e).find(".webform-tabs").once("exo.form.refresh").each(function(e){a(this).addClass("horizontal-tabs").wrap('<div class="exo-form-horizontal-tabs exo-form-element exo-form-element-js" />'),a(this).find(".item-list ul").addClass("horizontal-tabs-list").find("> li").addClass("horizontal-tab-button"),a(this).find("> .webform-tab").addClass("horizontal-tabs-pane").wrapAll('<div class="horizontal-tabs-panes" />')}).on("tabsbeforeactivate",function(e,o){o.oldPanel.hide(),o.newPanel.show()}),a(e).find(".exo-form-container-hide").each(function(){a(this).text().trim().length&&a(this).removeClass("exo-form-container-hide")}),i.once("exo.form").each(function(e,o){var o=a(o),t=(o.filter(".exo-form-wrap").each(function(e,o){"<"!==a(o).html().trim()[0]&&a(o).addClass("exo-form-wrap-pad")}),o.closest("[data-exo-theme]"));t.length&&o.removeClass(function(e,o){return(o.match(/(^|\s)exo-form-theme-\S+/g)||[]).join(" ")}).addClass("exo-form-theme-"+t.data("exo-theme"))}),this.once||(o=function(){i.find(".exo-form-inline").each(function(e,o){var o=a(o),t=0;o.removeClass("exo-form-inline-stack"),o.find("> *:visible").each(function(e,o){t+=a(o).outerWidth()}),t>n.Exo.$window.width()&&o.addClass("exo-form-inline-stack")})},this.once=!0,n.Exo.addOnResize("exo.form.core",o),n.Exo.event("ready").on("exo.form",function(e){n.Exo.event("ready").off("exo.form"),o()}))}}}(jQuery,Drupal);