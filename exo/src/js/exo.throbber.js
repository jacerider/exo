(function ($, window, Drupal, drupalSettings) {

  'use strict';

  /**
   * Prepare the Ajax request before it is sent.
   *
   * @param {XMLHttpRequest} xmlhttprequest The xml httpprequest.
   * @param {object} options The options.
   * @param {object} options.extraData The option extra data.
   */
  Drupal.Ajax.prototype.beforeSend = function (xmlhttprequest, options) {
    // For forms without file inputs, the jQuery Form plugin serializes the
    // form values, and then calls jQuery's $.ajax() function, which invokes
    // this handler. In this circumstance, options.extraData is never used. For
    // forms with file inputs, the jQuery Form plugin uses the browser's normal
    // form submission mechanism, but captures the response in a hidden IFRAME.
    // In this circumstance, it calls this handler first, and then appends
    // hidden fields to the form to submit the values in options.extraData.
    // There is no simple way to know which submission mechanism will be used,
    // so we add to extraData regardless, and allow it to be ignored in the
    // former case.
    if (this.$form) {
      options.extraData = options.extraData || {};

      // Let the server know when the IFRAME submission mechanism is used. The
      // server can use this information to wrap the JSON response in a
      // TEXTAREA, as per http://jquery.malsup.com/form/#file-upload.
      options.extraData.ajax_iframe_upload = '1';

      // The triggering element is about to be disabled (see below), but if it
      // contains a value (e.g., a checkbox, textfield, select, etc.), ensure
      // that value is included in the submission. As per above, submissions
      // that use $.ajax() are already serialized prior to the element being
      // disabled, so this is only needed for IFRAME submissions.
      var v = $.fieldValue(this.element);
      if (v !== null) {
        options.extraData[this.element.name] = v;
      }
    }

    // Disable the element that received the change to prevent user interface
    // interaction while the Ajax request is in progress. ajax.ajaxing prevents
    // the element from triggering a new request, but does not prevent the user
    // from changing its value.
    $(this.element).prop('disabled', true);
    Drupal.Exo.$body.addClass('ajax-loading');

    if (!this.progress || !this.progress.type) {
      return;
    }

    // Insert progress indicator.
    if (this.progress.type === 'throbber' && drupalSettings.exoLoader.alwaysFullscreen) {
      // Always show throbber as fullscreen overlay.
      this.progress.type = 'fullscreen';
    }
    var progressIndicatorMethod = 'setProgressIndicator' + this.progress.type.slice(0, 1).toUpperCase() + this.progress.type.slice(1).toLowerCase();
    if (progressIndicatorMethod in this && typeof this[progressIndicatorMethod] === 'function') {
      this[progressIndicatorMethod].call(this);
    }
  };

  /**
   * Overrides the throbber progress indicator.
   */
  Drupal.Ajax.prototype.setProgressIndicatorThrobber = function () {
    var _this = this;
    this.progress.element = $('<div class="ajax-progress ajax-progress-throbber" role="status"><div class="ajax-loader">' + drupalSettings.exoLoader.markup + '</div></div>');
    if (this.progress.message && !drupalSettings.exoLoader.hideAjaxMessage) {
      this.progress.element.find('.ajax-loader').after('<div class="message">' + this.progress.message + '</div>');
    }
    $(this.element).after(this.progress.element);
    setTimeout(function () {
      _this.progress.element.addClass('active');
    }, 10);
  };

  /**
   * Sets the fullscreen progress indicator.
   */
  Drupal.Ajax.prototype.setProgressIndicatorFullscreen = function () {
    var _this = this;
    this.progress.element = $('<div class="ajax-progress ajax-progress-fullscreen" role="status">' + drupalSettings.exoLoader.markup + '</div>');
    $(drupalSettings.exoLoader.throbberPosition).after(this.progress.element);
    setTimeout(function () {
      _this.progress.element.addClass('active');
    }, 10);
  };

  /**
   * Transition out.
   */
  Drupal.Ajax.prototype.successOriginal = Drupal.Ajax.prototype.success;
  Drupal.Ajax.prototype.success = function (response, status) {
    var _this = this;
    Drupal.Exo.$body.removeClass('ajax-loading');

    if (this.progress.element && this.progress.element.hasClass('active')) {
      this.progress.element.one(Drupal.Exo.transitionEvent, function () {
        Drupal.Ajax.prototype.successOriginal.call(_this, response, status);
      });
      this.progress.element.removeClass('active');
    }
    else {
      Drupal.Ajax.prototype.successOriginal.call(this, response, status);
    }
  };

})(jQuery, this, Drupal, drupalSettings);
