"use strict";!function(r,t,c){c.behaviors.exoAlchemistAdminAppearance={attach:function(e){var a=this,o=t.debounce(function(){a.submit()},200);r(".exo-alchemist-appearance-form").find("select, input:not(:text, :submit)").once("exo.alchemist").each(function(e,a){r(a).on("change",function(e){r(e.target).is(":not(:text, :submit)")&&o()})}),r(".exo-alchemist-appearance-form[data-exo-alchemist-revert]").once("exo.alchemist.revert").each(function(e,a){var o=r(".exo-modifier"),t=[];r(".exo-alchemist-appearance-form").closest(".exo-modal").length&&c.Exo.$window.on("exo-modal:onClosing.alchemist.appearance",function(e){c.Exo.$window.off("exo-modal:onClosing.alchemist.appearance"),o.each(function(e,a){r(a).attr("class",t[e]),c.Exo.$document.trigger("exoAlchemistAppearanceRefresh",[r(a)])})}),o.each(function(e,a){t.push(r(a).attr("class"))})})},submit:function(e){r("#exo-alchemist-appearance-refresh").first().trigger("mousedown")}},c.AjaxCommands.prototype.exoComponentModifierAttributes=function(e,a,o){for(var t in r(".exo-alchemist-appearance-form[data-exo-alchemist-revert] .exo-alchemist-revert-message").removeClass("hidden"),a.argument)if(a.argument.hasOwnProperty(t)){var n=a.argument[t],i=r('[data-exo-alchemist-modifier="'+n["data-exo-alchemist-modifier"]+'"]');n.hasOwnProperty("class")&&(i.removeClass(function(e,a){return(a.match(/(^|\s)exo-modifier--\S+/g)||[]).join(" ")}),i.addClass(n.class.join(" ")),void 0!==c.ExoAlchemistAdmin&&c.ExoAlchemistAdmin.watch()),c.Exo.$document.trigger("exoAlchemistAppearanceRefresh",[i])}}}(jQuery,_,Drupal);