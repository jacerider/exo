<?php

namespace Drupal\exo\Plugin\conditional_fields\handler;

use Drupal\conditional_fields\Plugin\conditional_fields\handler\OptionsButtons;

/**
 * Provides states handler for Check boxes/radio buttons.
 *
 * @ConditionalFieldsHandler(
 *   id = "states_handler_exo_options_buttons",
 * )
 */
class ExoOptionsButtons extends OptionsButtons {

  /**
   * {@inheritdoc}
   */
  public function statesHandler($field, $field_info, $options) {
    if (array_key_exists('#type', $field) && in_array($field['#type'], ['exo_checkbox', 'exo_checkboxes'])) {
      // Check boxes.
      return $this->checkBoxesHandler($field, $field_info, $options);
    }
    elseif (array_key_exists('#type', $field) && in_array($field['#type'], ['exo_radio', 'exo_radios'])) {
      // Radio.
      return $this->radioHandler($field, $field_info, $options);
    }
    return [];
  }

}
