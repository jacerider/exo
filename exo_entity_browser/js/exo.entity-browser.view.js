((d,l,w)=>{var p=[];l.behaviors.exoEntityBrowserView={attach:function(e,t){var n,s,o,i,r=d(".entity-browser-form"),a=(r.addClass("exo-entity-browser-view exo-no-animations"),r.attr("data-entity-browser-uuid")),c=d("#ief-dropzone-upload");c.length&&(c.children().length?(r.find(".js-form-type-dropzonejs").hide(),d("#edit-actions").show()):(c.hide(),d("#edit-actions").hide()),!l.views)||(n=l.views.instances[_.keys(l.views.instances)[0]],a&&n.$view.once("exo.entity-browser.view")&&(s=parent&&parent.drupalSettings&&parent.drupalSettings.entity_browser&&parent.drupalSettings.entity_browser[a]?parent.drupalSettings.entity_browser[a].cardinality:-1,(o=w.entity_browser_widget&&w.entity_browser_widget.auto_select)&&(n.$view.find(".views-field-entity-browser-select").show().parent().once("unbind-register-row-click").off("click").once("register-row-click"),(c=r.find(".entities-list-actions")).length||(c=d('<div class="entities-list-actions" />').appendTo(r)),(i=r.find(".entity-browser-use-selected, .entity-browser-show-selection").once("exo.entity-browser.view")).length)&&(c.empty(),i.appendTo(c)),parent&&parent.drupalSettings&&parent.drupalSettings.entity_browser&&parent.drupalSettings.entity_browser[a]&&parent.drupalSettings.entity_browser[a].entities&&parent.drupalSettings.entity_browser[a].entities.forEach(function(e){e=n.$view.find('input[value="'+e+'"]');e.length&&(e.prop("disabled",!0),e.closest(".views-field-entity-browser-select").parent().addClass("disabled checked"))}),p.forEach(function(e){e=n.$view.find('input[value="'+e+'"]');e.length&&(e.closest(".views-field-entity-browser-select").parent().addClass("checked"),e.prop("checked",!0))}),n.$view.find(".views-field-entity-browser-select").parent().once("exo.entity-browser.view").each(function(){var e=d(this),t=e.find(".views-field-entity-browser-select");d('<div class="exo-entity-browser-check"><svg class="exo-entity-browser-checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52"><circle class="exo-entity-browser-checkmark--circle" cx="26" cy="26" r="25" fill="none"/><path class="exo-entity-browser-checkmark--check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/></svg></div>').appendTo(t),t.find("input").prop("checked")&&e.addClass("checked")}).on("click.exo.entity-browser.view",function(e){e.preventDefault();var t=".views-field-entity-browser-select input",i=d(this),n=i.find(t),r=!n.prop("checked");n.prop("checked",r),r?(e.preventDefault(),0<s&&(e=d("tr.checked")).length>=s&&(e.first().removeClass("checked").addClass("unchecked"),e.first().find(t).prop("disabled",!1).prop("checked",!1)),o&&n.prop("disabled",!0),i.removeClass("unchecked").addClass("checked"),p.push(n.val())):(o&&n.prop("disabled",!1),i.removeClass("checked").addClass("unchecked"),p=p.filter(function(e){return e!==n.val()})),o&&(r?i.parents("form").find(".entities-list").trigger("add-entities",[[n.val()]]):(e=String(n.val()).split(":"),i.parents("form").find(".entities-list").find('[data-entity-id="'+e[1]+'"] .entity-browser-remove-selected-entity').trigger("click")))}),r.find(".entities-list .entity-browser-remove-selected-entity").once("exo.entity-browser.view").on("click.exo.entity-browser.view",function(e){var t=d(this).attr("data-remove-entity").replace(/^\D+/g,""),i=n.$view.find('input[value$="'+t+'"]');i.length&&(i.prop("checked",!1),i.closest(".views-field-entity-browser-select").parent().removeClass("checked"),p=p.filter(function(e){return e!==i.val()}))}),setTimeout(function(){r.removeClass("exo-no-animations")},300),n.$view.hasClass("exo-entity-browser-grid"))&&this.attachGrid(n))},attachGrid:function(e){e.$view.find(".views-row").once("exo-entity-browser-view").each(function(){var e=d(this),t=d('<div class="exo-entity-browser-info" />').appendTo(e);d(".views-field",e).each(function(e){d(this).find("img, input").length||d(this).appendTo(t)})})}}})(jQuery,Drupal,drupalSettings);