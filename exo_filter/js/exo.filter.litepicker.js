"use strict";!function(v){Drupal.behaviors.exoFilterLitepicker={attach:function(f,e){v(f).find(".exo-litepicker-input-start").once("exo-filter.litepicker").each(function(e,t){var n=v(t),i=n.closest(".exo-form-container-exo-litepicker-input"),a=n.data("litepicker-group"),r=v(f).find('.exo-litepicker-input-end[data-litepicker-group="'+a+'"]'),s=r.closest(".exo-form-container-exo-litepicker-input"),l=n.clone().removeAttr("name").removeAttr("id").attr("type","text").attr("aria-hidden","true").appendTo(r.parent());r.addClass("js-hide"),i.addClass("js-hide");var o=null,d=null,c=String(n.val()),p=String(r.val());c&&(o=new Date(c+"T00:00:00")),p&&(d=new Date(p+"T23:59:59"));var u=new Litepicker({element:l[0],firstDay:0,format:"MMMM D, YYYY",numberOfMonths:2,numberOfColumns:2,startDate:o,endDate:d,zIndex:9999,selectForward:!1,selectBackward:!1,splitView:!1,singleMode:!1,showWeekNumbers:!1,showTooltip:!0,disableWeekends:!0,resetButton:!0,plugins:["keyboardnav"]});u.on("render",function(e){var t=v(u.ui),a=v('<a href="" class="skip visually-hidden focusable" tabindex="1">Skip Calendar</a>');a.on("click",function(e){e.preventDefault(),u.hide(),i.removeClass("js-hide").addClass("skip-calendar"),r.removeClass("js-hide"),s.addClass("skip-calendar"),l.addClass("js-hide"),n.trigger("focus")}),t.find(".month-item-header").prepend(a)}),u.on("selected",function(e,t){var a=e.dateInstance,i=t.dateInstance;n.val(a.toISOString().split("T")[0]),r.val(i.toISOString().split("T")[0])}),u.on("clear:selection",function(){r.val(""),n.val("").trigger("change")})})}}}(jQuery);