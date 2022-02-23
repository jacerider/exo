<?php

namespace Drupal\exo_filter\Plugin\ExoFilter\filter;

use Drupal\exo_filter\Plugin\ExoFilterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'litepicker' formatter.
 *
 * @ExoFilter(
 *   id = "litepicker",
 *   label = @Translation("Litepicker"),
 *   field_types = {
 *     "search_api_date",
 *   }
 * )
 */
class Litepicker extends ExoFilterBase {

  /**
   * {@inheritDoc}
   */
  public function defaultConfiguration() {
    return [
      'part' => 'start',
      'group' => NULL,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function exposedElementSettingsForm(&$element) {
    $element['part'] = [
      '#type' => 'radios',
      '#title' => $this->t('Element Part'),
      '#options' => [
        'start' => $this->t('Start'),
        'end' => $this->t('End'),
      ],
      '#default_value' => $this->configuration['part'],
    ];
    $element['group'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Element Group'),
      '#description' => $this->t('A unique id that will group two fields together so they act as one date selector.'),
      '#default_value' => $this->configuration['group'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function exposedElementAlter(&$element, FormStateInterface $form_state, $context) {
    $element['#attached']['library'][] = 'exo_filter/litepicker';
    $element['#attributes']['class'][] = 'exo-litepicker-input';
    $element['#attributes']['class'][] = 'exo-litepicker-input-' . $this->configuration['part'];
    $element['#attributes']['data-litepicker-group'] = $this->configuration['group'];
    $element['#type'] = 'date';
    $element['#attributes']['type'] = 'date';
    if ($this->configuration['part'] === 'end') {
      $element['#element_validate'][] = [get_class($this), 'validateEnd'];
    }
    else {
      $element['#element_validate'][] = [get_class($this), 'validateStart'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function validateStart($element, FormStateInterface $form_state) {
    $value = $form_state->getValue($element['#parents']);
    if (!empty($value)) {
      $value .= 'T00:00:00';
      $form_state->setValue($element['#parents'], $value);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function validateEnd($element, FormStateInterface $form_state) {
    $value = $form_state->getValue($element['#parents']);
    if (!empty($value)) {
      $value .= 'T23:59:59';
      $form_state->setValue($element['#parents'], $value);
    }
  }

}
