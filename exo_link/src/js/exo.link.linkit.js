/**
 * @file
 * Linkit Autocomplete based on jQuery UI.
 */

(function ($, Drupal, _) {

  'use strict';

  var autocomplete;

  /**
   * JQuery UI autocomplete source callback.
   *
   * @param {object} request
   *   The request object.
   * @param {function} response
   *   The function to call with the response.
   */
  function sourceData(request, response) {
    var elementId = this.element.attr('id');

    if (!(elementId in autocomplete.cache)) {
      autocomplete.cache[elementId] = {};
    }

    /**
     * Transforms the data object into an array and update autocomplete results.
     *
     * @param {object} data
     *   The data sent back from the server.
     */
    function sourceCallbackHandler(data) {
      autocomplete.cache[elementId][term] = data.suggestions;
      response(data.suggestions);
    }

    // Get the desired term and construct the autocomplete URL for it.
    var term = request.term;

    // Check if the term is already cached.
    if (typeof autocomplete.cache[elementId][term] !== 'undefined') {
      response(autocomplete.cache[elementId][term]);
    }
    else {
      var options = $.extend({
        success: sourceCallbackHandler,
        data: {q: term}
      }, autocomplete.ajax);
      $.ajax(this.element.attr('data-autocomplete-path'), options);
    }
  }

  /**
   * Handles an autocomplete select event.
   *
   * @param {jQuery.Event} event
   *   The event triggered.
   * @param {object} ui
   *   The jQuery UI settings object.
   *
   * @return {boolean}
   *   False to prevent further handlers.
   */
  function selectHandler(event, ui) {
    var $context = $(event.target).closest('form,fieldset,tr');

    if (!ui.item.path) {
      throw 'Missing path param.' + JSON.stringify(ui.item);
    }

    $('input[name="href_dirty_check"]', $context).val(ui.item.path);

    if (ui.item.entity_type_id || ui.item.entity_uuid || ui.item.substitution_id) {
      if (!ui.item.entity_type_id || !ui.item.entity_uuid || !ui.item.substitution_id) {
        throw 'Missing path param.' + JSON.stringify(ui.item);
      }
    }
    $('input[name="attributes[href]"], input[name$="[attributes][href]"]', $context).val(ui.item.path);
    $('input[name="attributes[data-entity-type]"], input[name$="[attributes][data-entity-type]"]', $context).val(ui.item.entity_type_id);
    $('input[name="attributes[data-entity-uuid]"], input[name$="[attributes][data-entity-uuid]"]', $context).val(ui.item.entity_uuid);
    $('input[name="attributes[data-entity-substitution]"], input[name$="[attributes][data-entity-substitution]"]', $context).val(ui.item.substitution_id);

    if (ui.item.label) {
      // Automatically set the link title.
      var $linkTitle = $(event.target).closest('.form-item').siblings('.form-type-textfield').find('.linkit-widget-title');
      if ($linkTitle.length > 0) {
        if (!$linkTitle.val() || $linkTitle.hasClass('link-widget-title--auto')) {
          // Set value to the label.
          $linkTitle.val(ui.item.label);

          // Flag title as being automatically set.
          $linkTitle.addClass('link-widget-title--auto');
        }
      }
    }

    event.target.value = ui.item.value;

    return false;
  }

  /**
   * Override jQuery UI _renderItem function to output HTML by default.
   *
   * @param {object} ul
   *   The <ul> element that the newly created <li> element must be appended to.
   * @param {object} item
   *  The list item to append.
   *
   * @return {object}
   *   jQuery collection of the ul element.
   */
  function renderItem(ul, item) {
    var $line = $('<li>').addClass('linkit-result-line');
    var $wrapper = $('<div>').addClass('linkit-result-line-wrapper');
    $wrapper.append($('<span>').html(item.label).addClass('linkit-result-line--title'));

    if (typeof item.description !== 'undefined') {
      $wrapper.append($('<span>').html(item.description).addClass('linkit-result-line--description'));
    }
    return $line.append($wrapper).appendTo(ul);
  }

  /**
   * Override jQuery UI _renderMenu function to handle groups.
   *
   * @param {object} ul
   *   An empty <ul> element to use as the widget's menu.
   * @param {array} items
   *   An Array of items that match the user typed term.
   */
  function renderMenu(ul, items) {
    var self = this.element.autocomplete('instance');

    var grouped_items = _.groupBy(items, function (item) {
      return typeof item.group !== 'undefined' ? item.group : '';
    });

    $.each(grouped_items, function (group, items) {
      if (group.length) {
        ul.append('<li class="linkit-result-line--group ui-menu-divider">' + group + '</li>');
      }

      $.each(items, function (index, item) {
        self._renderItemData(ul, item);
      });
    });
  }

  function focusHandler() {
    return false;
  }

  function searchHandler(event) {
    var options = autocomplete.options;

    return !options.isComposing;
  }

  /**
   * Attaches the autocomplete behavior to all required fields.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches the autocomplete behaviors.
   * @prop {Drupal~behaviorDetach} detach
   *   Detaches the autocomplete behaviors.
   */
  Drupal.behaviors.exoLinkLinkit = {
    attach: function (context) {
      // Act on textfields with the "form-linkit-autocomplete" class.
      var $autocomplete = $(context).find('input.exo-link-linkit').once('linkit-autocomplete');
      if ($autocomplete.length) {
        $.widget('ui.autocomplete', $.ui.autocomplete, {
          _create: function () {
            this._super();
            this.widget().menu('option', 'items', '> :not(.linkit-result-line--group)');
          },
          _renderMenu: autocomplete.options.renderMenu,
          _renderItem: autocomplete.options.renderItem
        });

        // Use jQuery UI Autocomplete on the textfield.
        $autocomplete.autocomplete(autocomplete.options);

        $autocomplete.click(function () {
          var $this = $(this);
          $this.autocomplete('search', $this.val());
        });

        // Process each item.
        $autocomplete.each(function () {
          var $uri = $(this);
          $uri.autocomplete('widget').addClass('linkit-ui-autocomplete');

          $uri.closest('.form-item').siblings('.form-type-textfield').find('.linkit-widget-title')
            .each(function () {
              // Set automatic title flag if title is the same as uri text.
              var $title = $(this);
              var uriValue = $uri.val();
              if (uriValue && uriValue === $title.val()) {
                $title.addClass('link-widget-title--auto');
              }
            })
            .change(function () {
              // Remove automatic title flag.
              $(this).removeClass('link-widget-title--auto');
            });
        });

        $autocomplete.on('compositionstart.autocomplete', function () {
          autocomplete.options.isComposing = true;
        });
        $autocomplete.on('compositionend.autocomplete', function () {
          autocomplete.options.isComposing = false;
        });
      }
    },
    detach: function (context, settings, trigger) {
      if (trigger === 'unload') {
        $(context).find('input.form-linkit-autocomplete')
          .removeOnce('linkit-autocomplete')
          .autocomplete('destroy');
      }
    }
  };

  /**
   * Autocomplete object implementation.
   */
  autocomplete = {
    cache: {},
    options: {
      source: sourceData,
      focus: focusHandler,
      search: searchHandler,
      select: selectHandler,
      renderItem: renderItem,
      renderMenu: renderMenu,
      minLength: 1,
      isComposing: false
    },
    ajax: {
      dataType: 'json'
    }
  };

})(jQuery, Drupal, _);
