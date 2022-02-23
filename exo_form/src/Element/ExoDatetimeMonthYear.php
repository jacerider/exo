<?php

namespace Drupal\exo_form\Element;

use Drupal\Core\Datetime\Element\Datetime;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a datetime element.
 *
 * @FormElement("exo_datetime_month_year")
 */
class ExoDatetimeMonthYear extends Datetime {

  /**
   * {@inheritdoc}
   */
  public static function processDatetime(&$element, FormStateInterface $form_state, &$complete_form) {
    $element['#attached']['library'][] = 'exo_form/input-month';
    $element['#date_date_format'] = 'Y-m';
    $element['#date_date_element'] = 'month';
    $element = parent::processDatetime($element, $form_state, $completeForm);
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    if (!empty($input['date'])) {
      // Add first date of month.
      $input['date'] .= '-01';
    }
    return parent::valueCallback($element, $input, $form_state);
  }

}
