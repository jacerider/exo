(function ($, Drupal, document, displace) {

  let exoFormSelectCurrent:ExoFormSelect = null;

  interface ExoFormSelectValue {
    value: string;
    text: string;
    selected: boolean;
    group: boolean;
  }

  class ExoFormSelect {
    public uniqueId:string;
    public $element:JQuery;
    protected $field:JQuery;
    protected $error:JQuery;
    protected $trigger:JQuery;
    protected $wrapper:JQuery<HTMLElement | Text | Comment | Document>;
    protected $label:JQuery;
    protected $caret:JQuery;
    protected $hidden:JQuery;
    protected $dropdown:JQuery;
    protected $dropdownWrapper:JQuery;
    protected $dropdownScroll:JQuery;
    protected $dropdownList:JQuery;
    protected debug:boolean = false;
    protected open:boolean = false;
    protected supported:boolean;
    protected isSafari:boolean;
    protected multiple:boolean;
    protected placeholder:string;
    protected selected:Array<ExoFormSelectValue>;

    constructor($element:JQuery) {
      this.uniqueId = Drupal.Exo.guid();
      this.isSafari = Drupal.Exo.isSafari();
      this.supported = this.isSupported();
      this.$element = $element;
      this.$field = this.$element.find('select');
      this.multiple = (this.$field.attr('multiple')) ? true : false;
      if (this.hasError()) {
        this.$element.addClass('invalid');
      }
      this.$trigger = this.$element.find('.exo-form-select-trigger').attr('id', 'exo-form-select-trigger-' + this.uniqueId).prop('disabled', this.isDisabled());
      this.$wrapper = $element.find('.exo-form-select-wrapper');
      this.$caret = this.$element.find('.exo-form-select-caret');
      this.$label = $element.closest('.exo-form-select').find('label').first();
      this.placeholder = this.$field.attr('placeholder') || (this.multiple ? 'Select Multiple' : 'Select One');
      this.$label.attr('id', 'exo-form-select-label-' + this.uniqueId);
      this.$trigger.text(this.placeholder);
      this.$hidden = this.$element.find('.exo-form-select-hidden');
      if (this.supported) {
        this.$hidden
          .attr('id', 'exo-form-select-hidden-' + this.uniqueId)
          .attr('aria-labelledby', 'exo-form-select-label-' + this.uniqueId + ' exo-form-select-trigger-' + this.uniqueId + ' exo-form-select-hidden-' + this.uniqueId);
        if (this.multiple) {
          this.$hidden.attr('aria-label', 'Select Option');
        }
        else {
          this.$hidden.attr('aria-label', 'Select Options');
        }
        if (this.isDisabled()) {
          this.$hidden.prop('disabled', true).attr('tabindex', '-1');
        }
        // Copy tabindex
        if (this.$field.attr('tabindex')) {
          this.$hidden.attr('tabindex', this.$field.attr('tabindex'));
        }
        // Safari does not focus buttons by default with tab.
        if (!this.isSafari) {
          this.$field.attr('tabindex', '-1');
        }
        this.$dropdownWrapper = $('#exo-form-select-dropdown-wrapper');
        if (!this.$dropdownWrapper.length) {
          this.$dropdownWrapper = $('<div id="exo-form-select-dropdown-wrapper" class="exo-form"></div>');
          Drupal.Exo.getBodyElement().append(this.$dropdownWrapper);
        }
        this.$dropdown = $('<div class="exo-form-select-dropdown exo-form-input ' + this.$field.data('drupal-selector') + '" role="combobox" aria-owns="exo-form-select-list-' + this.uniqueId + '" aria-expanded="false"></div>');
        this.$dropdownScroll = $('<div class="exo-form-select-scroll"></div>').appendTo(this.$dropdown);
        this.$dropdownList = $('<ul id="exo-form-select-list-' + this.uniqueId + '" class="exo-form-select-list" role="listbox" aria-labelledby="exo-form-select-label-' + this.uniqueId + '" tabindex="-1"></ul>').appendTo(this.$dropdownScroll);
        this.$dropdownWrapper.append(this.$dropdown);
        this.$dropdown.addClass((this.multiple ? 'is-multiple' : 'is-single'));
        if (this.hasValue() === true) {
          this.$element.addClass('filled');
        }
      }
      else {
        this.$hidden.remove();
      }

      this.build();
      this.evaluate();
      this.bind();
      setTimeout(() => {
        this.$element.addClass('ready');
      });
    }

    public destroy() {
      this.unbind();
      this.$dropdown.remove();
      this.$element.removeData();
    }

    protected build() {
      this.loadOptionsFromSelect();
      this.updateTrigger();

      if (this.debug) {
        this.$field.show();
        setTimeout(() => {
          this.$trigger.trigger('tap');
        }, 500);
      }
    }

    protected evaluate() {
      if (this.supported) {
        var required = this.isRequired();
        var disabled = this.isDisabled();

        this.$field.prop('required', required);

        this.$trigger.prop('disabled', disabled);
        disabled ? this.$element.addClass('form-disabled') : this.$element.removeClass('form-disabled');
        if (this.multiple) {
          var $checkboxes = this.$dropdown.find('.exo-form-checkbox');
          disabled ? $checkboxes.addClass('form-disabled') : $checkboxes.removeClass('form-disabled');
          $checkboxes.find('.form-checkbox').prop('disabled', disabled);
        }
      }
    }

    protected bind() {
      this.$trigger.on('focus.exo.form.select', e => {
        // We blur as soon as the focus happens to avoid the cursor showing
        // momentarily within the field.
        this.$trigger.trigger('blur');
      })
      .on('click.exo.form.select', e => {
        e.preventDefault();
      })
      .on('tap.exo.form.select', e => {
        if (this.supported) {
          this.showDropdown();
        }
        else {
          e.preventDefault();
        }
      });

      this.$field.on('state:disabled.exo.form.select', e => {
        this.evaluate();
      }).on('state:required.exo.form.select', e => {
        this.evaluate();
      }).on('state:visible.exo.form.select', e => {
        this.evaluate();
      }).on('state:collapsed.exo.form.select', e => {
        this.evaluate();
      });

      const onInput = () => {
        if (this.hasValue()) {
          this.$element.addClass('value');
        }
        else {
          this.$element.removeClass('value');
        }
      }

      if (this.supported) {
        this.$dropdown.on('tap.exo.form.select', '.selector', e => {
          this.onItemTap(e);
        });
        this.$dropdown.on('tap.exo.form.select', '.close', e => {
          this.closeDropdown();
        });
        // Use focusin for IE support.
        this.$hidden.on('focusin.exo.form.select', e => {
          // Close existing.
          if (exoFormSelectCurrent !== null) {
            exoFormSelectCurrent.closeDropdown();
          }
          this.$wrapper.addClass('focused');
        }).on('blur.exo.form.select', e => {
          this.$wrapper.removeClass('focused');
        }).on('keydown.exo.form.select', e => {
          this.onHiddenKeydown(e);
        }).on('keyup.exo.form.select', e => {
          e.preventDefault();
        }).on('click.exo.form.select', e => {
          e.preventDefault();
          this.showDropdown();
        });

        this.$field.on('focus.exo.form.select', e => {
          if (this.isSafari) {
            this.$hidden.trigger('focus');
          }
        }).on('change.exo.form.select', e => {
          this.loadOptionsFromSelect();
          this.updateTrigger();
        }).on('input.exo.form.select', e => {
          onInput();
        });
        onInput();

        if (this.$field.attr('autofocus')) {
          this.showDropdown();
        }
      }
      else {
        this.$field.addClass('overlay');
        // On unsupported devies we rely on the device select widget and need
        // to update the trigger upon change.
        this.$field.on('change.exo.form.select', e => {
          this.loadOptionsFromSelect();
          this.updateTrigger();
        })
        .on('input.exo.form.select', e => {
          onInput();
        });
        onInput();
      }
    }

    protected unbind() {
      this.$element.off('.exo.form.select');
      this.$dropdown.off('.exo.form.select');
      this.$dropdown.find('.search-input').off('.exo.form.select');
      this.$field.off('.exo.form.select');
      $('body').off('.exo.form.select');
    }

    public onChange(e) {
    }

    public onItemTap(e) {
      var $item = $(e.currentTarget);
      var $wrapper = $item.parent();
      var option = $item.data('option');
      var action;

      if (!this.multiple) {
        $wrapper.find('.active, .selected').removeClass('active selected').removeAttr('aria-selected');
        $item.addClass('active selected').attr('aria-selected', 'true');
        this.changeSelected(option, 'add');
        return this.closeDropdown(true);
      }

      this.$dropdown.find('.selector.selected').removeClass('selected');
      if ($item.is('.active')) {
        action = 'remove';
        $item.removeClass('active');
        $item.find('input').prop('checked', false).trigger('change');
      }
      else {
        action = 'add';
        $item.addClass('active selected');
        $item.find('input').prop('checked', true).trigger('change');
      }
      return this.changeSelected(option, action);
    }

    public onSearchKeydown(e) {
      if (!this.open) {
        e.preventDefault();
        return;
      }
      var $item;

      // TAB - switch to another input.
      if (e.which === 9) {
        // Select current item.
        $item = this.$dropdown.find('.selector.selected');
        if ($item.length) {
          var option = $item.data('option');
          this.changeSelected(option, 'add');
        }

        // Focus on next visible field.
        var $inputs = this.$element.closest('form').find(':input').not('.ignore').not('[tabindex="-1"]');
        var $nextInput = null;
        var currentIndex = $inputs.index(this.$hidden);
        $inputs.each((index, element) => {
          if ($nextInput === null && index > currentIndex) {
            if ($(element).not('[tab-index="-1"]')) {
              $nextInput = $(element);
            }
          }
        });
        if ($nextInput !== null) {
          $nextInput.focus();
          e.preventDefault();
        }
        return this.closeDropdown();
      }

      // ESC - close dropdown.
      if (e.which === 27) {
        return this.closeDropdown(true);
      }

      // ENTER - select option and close when select this.$options are opened
      if (e.which === 13) {
        $item = this.$dropdown.find('.selector.selected');
        if ($item.length) {
          $item.trigger('tap');
        }
        e.preventDefault();
      }

      // ARROW DOWN or RIGHT - move to next not disabled or hidden option
      if (e.which === 40 || e.which === 39) {
        this.highlightOption(this.$dropdown.find('.selector.selected').nextAll('.selector:not(.hide):visible').first(), true, true);
        e.preventDefault();
      }

      // ARROW UP or LEFT - move to next not disabled or hidden option
      if (e.which === 38 || e.which === 37) {
        this.highlightOption(this.$dropdown.find('.selector.selected').prevAll('.selector:not(.hide):visible').first(), true, true);
        e.preventDefault();
      }
    }

    public onSearchKeyup(e) {
      if (!this.open) {
        e.preventDefault();
        return;
      }

      // When user types letters or numbers or delete.
      if (this.isAlphaNumberic(e.which) || e.which === 8) {
        const $item = $(e.currentTarget);
        const search = $item.val().toString().toLowerCase();
        if (search) {
          let $items = this.$dropdown.find('.selector');
          if (this.multiple) {
            $items = $items.filter(':not(.active)');
          }
          $items.each((index, element) => {
            const text = $(element).data('option').text.toLowerCase();
            if (text.indexOf(search) >= 0) {
              $(element).removeClass('hide');
            }
            else {
              $(element).addClass('hide');
            }
          });
          this.$dropdown.find('.optgroup').removeClass('hide').each((index, element) => {
            const $optgroup = $(element);
            if (!$optgroup.nextUntil('.optgroup').filter(':not(.hide)').length) {
              $optgroup.addClass('hide');
            }
          });
        }
        else {
          this.$dropdown.find('.hide').removeClass('hide');
        }
        this.highlightOption(this.$dropdown.find('.selector:not(.hide):visible').first());
      }
      e.preventDefault();
    }

    public isAlphaNumberic(key) {
      var inp = String.fromCharCode(key);
      return /[a-zA-Z0-9-_ ]/.test(inp);
    }

    public onHiddenKeydown(e) {
      if (!this.open) {

        // If ctrl, alt, or command key held.
        if (e.metaKey === true) {
          return;
        }

        // TAB - switch to another input.
        if (e.which === 9) {
          return;
        }

        // Left.
        if (e.which === 37 && !this.multiple) {
          var $item = this.$dropdown.find('.selector.selected').prevAll('.selector:not(.hide):visible').first();
          this.highlightOption($item, true, true);
          var option = $item.data('option');
          this.changeSelected(option, 'add');
          e.preventDefault();
          return;
        }

        // Right.
        if (e.which === 39 && !this.multiple) {
          var $item = this.$dropdown.find('.selector.selected').nextAll('.selector:not(.hide):visible').first();
          this.highlightOption($item, true, true);
          var option = $item.data('option');
          this.changeSelected(option, 'add');
          e.preventDefault();
          return;
        }

        // Is not alpha/numeric/up/down.
        if (!this.isAlphaNumberic(e.which) && e.which !== 38 && e.which !== 40) {
          e.preventDefault();
          return;
        }

        // ARROW DOWN WHEN SELECT IS CLOSED - open dropdown.
        if ((e.which === 38 || e.which === 40)) {
          e.which = 13;
          e.preventDefault();
          this.showDropdown();
          return;
        }

        // ENTER WHEN SELECT IS CLOSED - submit form.
        if (e.which === 13) {
          return;
        }

        if (e.which === 39 || e.which === 37) {
          e.preventDefault();
          return;
        }

        // Screen reader support.
        if (e.which === 17 || e.which === 18 || e.which === 32) {
          e.preventDefault();
          return;
        }

        // When user types letters.
        var nonLetters = [9, 13, 27, 37, 38, 39, 40];
        if ((nonLetters.indexOf(e.which) === -1)) {
          e.preventDefault();
          this.showDropdown();
          var code = e.which || e.which;
          var character = String.fromCharCode(code).toLowerCase();
          this.$dropdown.find('.search-input').val(character);
          this.onSearchKeyup(e);
        }
        e.preventDefault();
      }
    }

    public populateDropdown() {
      this.$dropdownList.find('li').remove();

      if (this.$dropdown.find('.search-input').length === 0) {
        this.$dropdown
          .prepend('<div class="close" aria-label="Close">&times;</div>')
          .prepend('<div class="search"><input type="text" class="exo-form-input-item simple search-input" aria-autocomplete="list" aria-controls="exo-form-select-scroll-' + this.uniqueId + '" tabindex="-1"></input></div>')
          .find('.search-input').attr('placeholder', this.placeholder).on('keydown.exo.form.select', e => {
            this.onSearchKeydown(e);
          }).on('keyup.exo.form.select', e => {
            this.onSearchKeyup(e);
          });
      }
      this.$dropdown.find('.search-input').attr('placeholder', 'Search...');
      var options = this.getAllOptions();
      var optionsEnabled = this.$field.data('options-enabled') || [];
      var optionsDisabled = this.$field.data('options-disabled') || [];
      var $disabled = $('<ul />');
      for (var i = 0; i < options.length; i++) {
        var option = options[i];
        if (option['value'] === '' && this.multiple === true) {
          continue;
        }
        const checkboxId = 'exo-form-option-' + this.uniqueId + '-' + i;
        const liClass = 'exo-form-option-' + option.text.replace(/\W/g, '-').toLowerCase();

        var li = $('<li role="option" class="' + liClass + '" role="listitem" tabindex="-1"></li>');

        if (option.group) {
          li.addClass('optgroup');
          li.html('<span>' + option.text + '</span>');
        }
        else if (this.multiple) {
          // Do not show empty value.
          if (!this.isRequired() && option.value === '_none') {
            continue
          }
          li.addClass('selector exo-form-checkbox ready');
          li.html('<span><input id="' + checkboxId + '" type="checkbox" class="form-checkbox"><label for="' + checkboxId + '" class="option">' + option.text + '<div class="exo-ripple"></div></label></span>');
        }
        else {
          li.addClass('selector');
          li.html('<span>' + option.text + '</span>');
        }

        if (option.selected) {
          li.addClass('active').attr('aria-selected', 'true');
          li.find('input').prop('checked', true);
        }

        li.data('option', option);

        if (option.value && optionsEnabled.length && !optionsEnabled.includes(option.value) && option.value !== '_none' && option.value !== '_all') {
          li.addClass('disabled');
          $disabled.append(li);
          continue;
        }

        if (option.value && optionsDisabled.length && optionsDisabled.includes(option.value)) {
          li.addClass('disabled');
          $disabled.append(li);
          continue;
        }

        this.$dropdownList.append(li);
      }
      if ($disabled.children().length) {
        var optionsDisabledLabel = this.$field.data('options-disabled-label');
        if (optionsDisabledLabel) {
          this.$dropdownList.append($('<li class="selector-disabled" role="listitem" tabindex="-1"><span>' + optionsDisabledLabel + '</span></li>'));
        }
        this.$dropdownList.append($disabled.children());
      }

      if (this.multiple) {
        this.$dropdownList.find('.form-checkbox').on('change', e => {
          this.highlightOption($(e.currentTarget).closest('.selector'), false);
        });
      }

      this.highlightOption();

      Drupal.attachBehaviors(this.$dropdownList[0]);
    }

    public getAllOptions(field?) {
      if (!field) {
        return this.selected;
      }
      var vals = [];
      for (var i = 0; i < this.selected.length; i++) {
        vals.push(this.selected[i][field]);
      }
      return vals;
    }

    public loadOptionsFromSelect() {
      this.selected = [];
      this.$field.find('option, optgroup').each((index, element) => {
        const $item = $(element);
        var values:ExoFormSelectValue = {
          value: '',
          text: '',
          selected: false,
          group: false
        };
        if ($item.is('optgroup')) {
          values.text = $(element).attr('label');
          values.group = true;
        }
        else {
          values.value = $item.attr('value');
          values.text = $item.html();
          values.selected = $item.is(':selected');
        }
        if (this.multiple && (values.value === '' || values.value === '_none')) {
          $item.remove();
        }
        else {
          this.selected.push(values);
        }
      });
    }

    public updateTrigger() {
      var value = this.getSelectedOptions('value').join('');
      if (value === null || value === '' || value === '_none') {
        this.$trigger.val('');
        this.$trigger.attr('placeholder', this.htmlDecode(this.getSelectedOptions('text').join(', ')));
      }
      else {
        // This change was made because it caused reload issues within Webform
        // email handler screens. It caused the page to reload indefiniately.
        // I do not believe it is used by anything so it has been removed.
        this.$trigger.val(this.htmlDecode(this.getSelectedOptions('text').join(', ')));
      }
    }

    public getSelectedOptions(field:string):Array<any> {
      var vals = [];
      for (var i = 0; i < this.selected.length; i++) {
        if (this.selected[i].selected) {
          if (field) {
            vals.push(this.selected[i][field]);
          }
          else {
            vals.push(this.selected[i]);
          }
        }
      }
      return vals;
    }

    public changeSelect(option, action) {
      var found = false;
      for (var i = 0; i < this.selected.length; i++) {
        if (!this.multiple) {
          this.selected[i].selected = false;
        }
        if (this.selected[i].value === option.value) {
          found = true;
          if (action === 'add') {
            this.selected[i].selected = true;
          }
          else if (action === 'remove') {
            this.selected[i].selected = false;
          }
        }
      }

      this.updateTrigger();
      if (this.multiple) {
        this.updateSearch();
      }
      this.updateSelect((!found) ? option : null);
    }

    public updateSelect(newOption:ExoFormSelectValue) {
      if (newOption) {
        var option = $('<option></option>')
          .attr('value', newOption.value)
          .html(newOption.text);
        this.$field.append(option);
      }

      this.$field.val(this.getSelectedOptions('value'));
      this.$field[0].dispatchEvent(new Event('change', {'bubbles':true, 'cancelable':false}));
      this.$field[0].dispatchEvent(new Event('input', {'bubbles':true, 'cancelable':false}));
    }

    public changeSelected(option, action) {
      var found = false;
      var notEmpty = false;
      for (var i = 0; i < this.selected.length; i++) {
        if (!this.multiple) {
          this.selected[i].selected = false;
        }
        if (this.selected[i].value === option.value) {
          found = true;
          if (action === 'add') {
            this.selected[i].selected = true;
          }
          else if (action === 'remove') {
            this.selected[i].selected = false;
          }
        }
        if (this.multiple) {
          if ((this.selected[i].value !== '' || this.selected[i].value !== '_none') && this.selected[i].selected) {
            notEmpty = true;
          }
        }
      }

      if (this.multiple) {
        for (var i = 0; i < this.selected.length; i++) {
          if (this.selected[i].value === '' || this.selected[i].value === '_none') {
            this.selected[i].selected = !notEmpty;
          }
        }
      }

      this.updateTrigger();
      if (this.multiple) {
        this.updateSearch();
      }
      this.updateSelect((!found) ? option : null);
    }

    public updateSearch() {
      this.$dropdown.find('.search-input').attr('placeholder', this.getSelectedOptions('text').join(', '));
    }

    public highlightOption($item?:JQuery, scroll?:boolean, force?:boolean) {
      scroll = scroll !== false;
      $item = $item || this.$dropdownList.find('.selector.active:eq(0)');
      if (!$item.length && force) {
        $item = this.$dropdownList.find('.selector:eq(0)');
      }
      if ($item.length) {
        this.$dropdown.find('.selector.selected').removeClass('selected').removeAttr('aria-selected');
        $item.addClass('selected').attr('aria-selected', 'true');
        this.$dropdown.find('.search-input').attr('aria-activedescendant', $item.attr('id'));
        this.$hidden.attr('aria-activedescendant', $item.attr('id'));
        if (scroll) {
          this.highlightScrollTo($item);
        }
      }
    }

    public highlightScrollTo($item?:JQuery) {
      $item = $item || this.$dropdown.find('.selector.selected');
      if ($item.length) {
        var scrollTop = this.$dropdownScroll.scrollTop();
        var scrollHeight = this.$dropdownScroll.outerHeight();
        var scrollOffset = this.$dropdownScroll.offset().top;
        var itemOffset = $item.offset().top;
        var itemHeight = $item.outerHeight();
        var itemPosition = scrollTop + itemOffset - scrollOffset;

        if (itemPosition + itemHeight > scrollHeight + scrollTop) {
          this.$dropdownScroll.scrollTop(itemPosition + itemHeight - scrollHeight);
        }
        else if (itemPosition < scrollTop) {
          this.$dropdownScroll.scrollTop(itemPosition);
        }
      }
    }

    public hasValue() {
      var value = this.$field.val();
      if (value && typeof value === 'object') {
        if (value.length === 1 && value[0] === '') {
          return false;
        }
        return value.length > 0;
      }
      return value !== '' && value !== '- Any -' && value !== '_none';
    }

    public hasError() {
      return this.$field.hasClass('error');
    }

    public showDropdown() {
      $('body').trigger('tap');
      if (this.open) {
        return this.closeDropdown();
      }

      // Always populate the dropdown before showing.
      this.populateDropdown();
      this.open = true;
      exoFormSelectCurrent = this;

      this.$dropdownList.css('max-height', '');

      this.positionDropdown();
      Drupal.Exo.lockOverflow(this.$dropdown);
      Drupal.Exo.showShadow({
        opacity: .2,
      });
      this.$element.addClass('active');
      this.$wrapper.addClass('focused');
      this.$dropdown.addClass('active').find('.search-input').focus();
      this.$dropdownWrapper.attr('class', this.$element.closest('form').attr('class')).addClass('exo-form-select-dropdown-wrapper').css({padding: 0, margin: 0});
      this.$dropdownScroll.scrollTop(0);
      this.highlightScrollTo();

      setTimeout(() => {
        this.$element.addClass('animate');
        this.$dropdown.addClass('animate').attr('aria-expanded', 'true');
      }, 50);
      this.windowHideDropdown();
    }

    public windowHideDropdown() {
      $('body').on('tap' + '.' + this.uniqueId, e => {
        if (!this.open) {
          return;
        }
        if ($(e.target).closest(this.$dropdown).length) {
          return;
        }
        this.closeDropdown();
      });
    }

    public positionDropdown() {
      if (this.open === true) {
        const windowTop = Drupal.Exo.$window.scrollTop();
        const windowHeight = Drupal.Exo.$window.height();
        const windowBottom = windowTop + windowHeight;
        const dropdownTop = this.$wrapper.offset().top;
        let dropdownHeight = this.$dropdown.outerHeight();
        const dropdownBottom = dropdownTop + dropdownHeight;
        const fixedHeaderHeight = ($('.exo-fixed-header .exo-fixed-element').outerHeight() || 0) + displace.offsets.top;

        this.$dropdown.removeClass('from-top from-bottom').css({
          left: this.$trigger.offset().left,
          width: this.$trigger.outerWidth(),
        });
        let direction = 'top';

        // Check if dropdown can fit in available window.
        if (windowHeight - fixedHeaderHeight < dropdownHeight) {
          dropdownHeight = windowHeight - fixedHeaderHeight - this.$dropdown.find('.search-input').height() - 20;
          this.$dropdownList.css('max-height', dropdownHeight);
          Drupal.Exo.$window.scrollTop(dropdownTop - fixedHeaderHeight - 10);
        }
        else {
          if (windowBottom > dropdownBottom) {
            // Do nothing. Can move down.
          }
          else if (windowTop + fixedHeaderHeight < dropdownTop - dropdownHeight) {
            // Can move up.
            direction = 'bottom';
          }
          else {
            // Can't fully move up or down. Allow down but scoll.
            const available = windowBottom - (windowTop + fixedHeaderHeight);
            Drupal.Exo.$window.scrollTop(dropdownTop - fixedHeaderHeight - ((available - dropdownHeight) / 2));
          }
        }

        switch (direction) {
          case 'top':
            this.$dropdown.addClass('from-top').css('top', this.$trigger.offset().top);
            break;
          case 'bottom':
            let offset = Drupal.Exo.$document.height() - (this.$trigger.offset().top + this.$trigger.outerHeight());
            this.$dropdown.addClass('from-bottom').css('bottom', offset);
            break;
        }

        if (this.multiple && this.$dropdownScroll[0].scrollHeight > this.$dropdownScroll.height() * 2) {
          const scrollWidth = this.$dropdownScroll.outerWidth();
          const scrollOffsetLeft = this.$dropdownScroll.offset().left;
          const scrollOffsetRight = scrollOffsetLeft + scrollWidth;
          const newScrollColumnWidth = 260;
          const newScrollWidth = newScrollColumnWidth * 3;
          if (scrollOffsetLeft + newScrollWidth < Drupal.Exo.$window.width()) {
            this.$dropdownList.addClass('column--3');
            this.$dropdownScroll.css({
              marginRight: (newScrollWidth - scrollWidth) * -1,
              width: newScrollWidth,
              minWidth: newScrollWidth,
              maxWidth: newScrollWidth,
            });
          }
          else if (scrollOffsetRight - newScrollWidth > 0) {
            this.$dropdownList.addClass('column--3');
            this.$dropdownScroll.css({
              marginLeft: (newScrollWidth - scrollWidth) * -1,
              width: newScrollWidth,
              minWidth: newScrollWidth,
              maxWidth: newScrollWidth,
            });
          }
        }
      }
    }

    public closeDropdown(focus?:boolean) {
      if (this.open === true) {
        this.open = false;
        exoFormSelectCurrent = null;
        this.$dropdown.attr('aria-expanded', 'false');
        this.$dropdown.removeClass('animate').find('.search-input').val('');
        this.$element.removeClass('animate');
        this.$wrapper.removeClass('focused');
        this.updateSearch();
        Drupal.Exo.hideShadow();
        $('body').off('.' + this.uniqueId);
        setTimeout(() => {
          this.$dropdownList.find('.hide').removeClass('hide');
          Drupal.Exo.unlockOverflow(this.$dropdown);
          if (this.hasValue() === true) {
            this.$element.addClass('filled');
          }
          else {
            this.$element.removeClass('filled');
          }
          if (this.open === false) {
            this.$element.removeClass('active');
            this.$dropdown.removeClass('active').removeAttr('style');
            this.$dropdownWrapper.removeAttr('class');
          }
        }, 350);
        if (focus) {
          setTimeout(() => {
            this.$hidden.trigger('focus', [1]);
          });
        }
      }
    }

    public htmlDecode(value:string):string {
      return $('<div/>').html(value).text().replace(/&amp;/g, '&');
    }

    public isRequired():boolean {
      var required = this.$field.attr('required');
      return typeof required !== 'undefined';
    }

    public isDisabled():boolean {
      return this.$field.is(':disabled');
    }

    public isSupported():boolean {
      if (Drupal.Exo.isIE() === true) {
        return document.documentMode >= 8;
      }
      return !Drupal.Exo.isTouch();
    }
  }

  /**
   * Toolbar build behavior.
   */
  Drupal.behaviors.exoFormSelect = {
    instances: {},
    once: false,

    attach: function(context) {
      $(context).find('.form-item.exo-form-select-js').once('exo.form.select').each((index, element) => {
        const select = new ExoFormSelect($(element));
        Drupal.behaviors.exoFormSelect.instances[select.uniqueId] = select;
      });
      if (this.once === false) {
        this.once === true;
        Drupal.Exo.addOnResize('exo.form', function () {
          for (const key in Drupal.behaviors.exoFormSelect.instances) {
            if (Drupal.behaviors.exoFormSelect.instances.hasOwnProperty(key)) {
              const select = Drupal.behaviors.exoFormSelect.instances[key];
              select.positionDropdown();
            }
          }
        });
      }
    },

    detach: function (context, settings, trigger) {
      if (trigger === 'unload' && context !== document) {
        const $selects = $(context).find('.form-item.exo-form-select-js');
        for (const key in Drupal.behaviors.exoFormSelect.instances) {
          if (Drupal.behaviors.exoFormSelect.instances.hasOwnProperty(key)) {
            const select = Drupal.behaviors.exoFormSelect.instances[key];
            if (select.$element.is($selects)) {
              select.destroy();
              delete Drupal.behaviors.exoFormSelect.instances[key];
            }
          }
        }
      }
    }
  }

})(jQuery, Drupal, document, Drupal.displace);
