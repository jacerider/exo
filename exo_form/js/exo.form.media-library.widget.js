"use strict";!function(i,a){a.behaviors.ExoFormMediaLibraryWidgetSortable={attach:function(a){i("#media-library-wrapper").once("exo.form.media-library").each(function(a,r){var e=i(r);e.find(".media-library-menu").length&&e.addClass("has-media-library-menu")}),i("#media-library-wrapper .media-library-menu li.active").removeClass(),i("#media-library-wrapper .media-library-menu a.active").parent().addClass("active")}}}(jQuery,Drupal,drupalSettings);