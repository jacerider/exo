document.getElementsByTagName("BODY")[0].style.display="none",(e=>{parent.jQuery(parent.document).find("iframe[data-uuid*="+e.entity_browser.exo_iframe.uuid+"]").hide().prev().hide().parent().find("a[data-uuid*="+e.entity_browser.exo_iframe.uuid+"]").trigger("entities-selected",[e.entity_browser.exo_iframe.uuid,e.entity_browser.exo_iframe.entities]).unbind("entities-selected").show()})(drupalSettings);