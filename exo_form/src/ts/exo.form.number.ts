(function ($, Drupal) {

  class ExoFormNumber {
    protected $element:JQuery;
    protected $input:JQuery;

    constructor($element:JQuery) {
      this.$element = $element;
      this.$input = this.$element.find('input[type="number"]');
      const amount = this.$input.prop('step');

      this.$element.find('.field-prefix').on('click.exo.form.number', e => {
        this.adjust('decrease', amount);
      });

      this.$element.find('.field-suffix').on('click.exo.form.number', e => {
        this.adjust('increase', amount);
      });

      this.$input.on('keypress.exo.form.number', function (e) {
        return (e.charCode == 8 || e.charCode == 0 || e.charCode == 13) ? null : (e.charCode >= 48 && e.charCode <= 57) || e.charCode === 46;
      });
    }

    public adjust(op:string, amount?:string) {
      amount = amount || '1';
      const oldValue:string = this.$input.val() as string || '0';
      let newValue = 0;
      switch (op) {
        case 'increase':
          newValue = parseFloat(oldValue) + parseFloat(amount);
          break;
        case 'decrease':
          newValue = parseFloat(oldValue) - parseFloat(amount);
          if (newValue < 0) {
            newValue = 0;
          }
          break;
      }
      this.$input.val(newValue).trigger('change');
    }
  }

  /**
   * Toolbar build behavior.
   */
  Drupal.behaviors.exoFormNumber = {
    attach: function(context) {
      $(context).find('.exo-form-number-js').once('exo.form.number').each((index, element) => {
        new ExoFormNumber($(element));
      });
    }
  }

})(jQuery, Drupal);
