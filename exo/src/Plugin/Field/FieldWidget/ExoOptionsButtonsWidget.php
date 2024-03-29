<?php

namespace Drupal\exo\Plugin\Field\FieldWidget;

use Drupal\Core\Field\Plugin\Field\FieldWidget\OptionsButtonsWidget;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\field_ui\Form\FieldConfigEditForm;

/**
 * Plugin implementation of the 'options_buttons' widget.
 *
 * @FieldWidget(
 *   id = "exo_options_buttons",
 *   label = @Translation("eXo check boxes/radio buttons"),
 *   field_types = {
 *     "boolean",
 *     "entity_reference",
 *     "list_integer",
 *     "list_float",
 *     "list_string",
 *   },
 *   multiple_values = TRUE
 * )
 */
class ExoOptionsButtonsWidget extends OptionsButtonsWidget {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'style' => 'inline',
      'empty_label' => 'Auto',
      'reverse' => FALSE,
      'hide_empty' => FALSE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);
    $form['style'] = [
      '#type' => 'select',
      '#title' => $this->t('Display Style'),
      '#options' => exo_element_style_types(),
      '#default_value' => $this->getSetting('style'),
    ];
    $form['empty_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Empty Label'),
      '#default_value' => $this->getSetting('empty_label'),
      '#required' => TRUE,
    ];
    $form['reverse'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Reverse Options'),
      '#description' => $this->t('Reverse the order of the options.'),
      '#default_value' => $this->getSetting('reverse'),
    ];
    $form['hide_empty'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Hide empty option'),
      '#default_value' => $this->getSetting('hide_empty'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $style_options = exo_element_style_types();
    $style = $this->getSetting('style');
    $summary = parent::settingsSummary();
    $summary[] = $this->t('Display style: %style', [
      '%style' => $style_options[$style] ?? $style,
    ]);
    if ($this->getSetting('reverse')) {
      $summary[] = $this->t('Reverse option order');
    }
    if ($this->getSetting('hide_empty')) {
      $summary[] = $this->t('Hide empty option');
    }
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $element['#exo_style'] = $this->getSetting('style');
    $selected = $this->getSelectedOptions($items);

    if ($this->required && $this->getSetting('hide_empty') && $items->getEntity()->isNew() && count($selected) === 1) {
      $selected = NULL;
    }

    if ($this->multiple) {
      $element['#type'] = 'exo_checkboxes';
    }
    else {
      $element['#type'] = 'exo_radios';
      $element['#default_value'] = $selected ? reset($selected) : ($this->required ? NULL : '_none');
    }

    if ($this->getSetting('reverse')) {
      $element['#options'] = array_reverse($element['#options'], TRUE);
    }

    if ($this->getSetting('hide_empty') && !$form_state->getFormObject() instanceof FieldConfigEditForm) {
      $element['#attributes']['class'][] = 'hide-empty';
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEmptyLabel() {
    if (!$this->required && !$this->multiple) {
      return $this->getSetting('empty_label');
    }
  }

}
