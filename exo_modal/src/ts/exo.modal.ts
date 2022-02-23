(function ($, _, Drupal, drupalSettings, displace) {

  TSinclude('./exo.modal/_exo.modal.ts')
  TSinclude('./exo.modal/_exo.modals.ts')

  /**
   * Modal build behavior.
   */
  Drupal.behaviors.exoModal = {
    attach: function(context) {
      Drupal.ExoModal.attach(context);
      const focusedModal = Drupal.ExoModal.getVisibleFocus();
      if (focusedModal && focusedModal.getElement().find(context).length) {
        focusedModal.refreshContent();
      }
    }
  }

})(jQuery, _, Drupal, drupalSettings, Drupal.displace);
