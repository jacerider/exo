(function ($, _, Drupal, drupalSettings) {

  Drupal.Exo.event('reveal').on('exo.aos', e => {

    /**
     * AOS build behavior.
     */
    Drupal.behaviors.exoAos = {
      attach: function(context) {
        if (drupalSettings.exoAos) {
          let settings = {};
          if (drupalSettings.exoAos.defaults && typeof drupalSettings.exoAos.defaults === 'object') {
            settings = drupalSettings.exoAos.defaults;
          }
          $(context).find('[data-aos]').once('exo.aos').on(Drupal.Exo.transitionEvent + '.exo.aos', e => {
            const event = new Event('aos:finish');
            document.dispatchEvent(event);
          });
          AOS.init(settings);
          delete drupalSettings.exoAos;
        }
      }
    }
    Drupal.behaviors.exoAos.attach(document.body);
  });

})(jQuery, _, Drupal, drupalSettings);
