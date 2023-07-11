<?php

namespace Drupal\exo_form\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\tablefield\Plugin\Field\FieldWidget\TablefieldWidget;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * Plugin implementation of the 'tablefield' widget.
 *
 * @FieldWidget (
 *   id = "exo_tablefield",
 *   label = @Translation("eXo: Table Field"),
 *   field_types = {
 *     "tablefield"
 *   },
 * )
 */
class ExoTablefieldWidget extends TablefieldWidget {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'wrapper' => 'fieldset',
      'hide_caption' => TRUE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    $element['wrapper'] = [
      '#type' => 'select',
      '#title' => $this->t('Wrapper'),
      '#default_value' => $this->getSetting('wrapper'),
      '#options' => $this->getWrapperOptions(),
      '#required' => TRUE,
    ];

    $element['hide_caption'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Hide caption'),
      '#description' => $this->t('This will hide the caption field.'),
      '#default_value' => $this->getSetting('hide_caption'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    $summary[] = $this->t('Wrapper type: @value', ['@value' => $this->getWrapperOptions()[$this->getSetting('wrapper')]]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  protected function getWrapperOptions() {
    return [
      'fieldset' => $this->t('Fieldset'),
      'details' => $this->t('Details'),
      'container' => $this->t('Container'),
      'item' => $this->t('Item'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    if (!empty($element['#default_value'])) {
      foreach ($element['#default_value'] as $i => $row) {
        if (is_array($row)) {
          foreach ($row as $ii => $column) {
            if (is_string($column) && $column === '#colspan#') {
              $element['#default_value'][$i][$ii] = '';
            }
          }
        }
      }
    }

    $element = [
      '#type' => $this->getSetting('wrapper'),
      '#title' => $element['#title'],
      '#description' => $element['#description'],
      'value' => $element,
    ];
    $element['value']['#title'] = '';
    $element['value']['#description'] = '';

    if ($this->getSetting('hide_caption')) {
      $element['value']['caption']['#access'] = FALSE;
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function extractFormValues(FieldItemListInterface $items, array $form, FormStateInterface $form_state) {
    $field_name = $this->fieldDefinition->getName();

    $trigger = $form_state->getTriggeringElement();
    $cleanup = TRUE;
    if (isset($trigger['#submit'][0][0]) && isset($trigger['#submit'][0][1])) {
      if ($trigger['#submit'][0][0] === 'Drupal\tablefield\Element\Tablefield' && $trigger['#submit'][0][1] === 'submitCallbackRebuild') {
        $cleanup = FALSE;
      }
    }

    if ($cleanup) {
      // Extract the values from $form_state->getValues().
      $path = array_merge($form['#parents'], [$field_name]);
      $key_exists = NULL;
      $values = NestedArray::getValue($form_state->getValues(), $path, $key_exists);
      if ($key_exists) {
        $is_empty = TRUE;
        if (!empty($values[0]['value']['tablefield']['table'])) {
          foreach ($values[0]['value']['tablefield']['table'] as $key => $row) {
            unset($row['weight']);
            $row_empty = TRUE;
            foreach ($row as $key2 => $column) {
              if (!empty($column)) {
                $is_empty = FALSE;
                $row_empty = FALSE;
              }
              elseif ($key !== 0) {
                $values[0]['value']['tablefield']['table'][$key][$key2] = '#colspan#';
              }
            }
            if ($row_empty) {
              unset($values[0]['value']['tablefield']['table'][$key]);
            }
          }
        }
        if ($is_empty) {
          $form_state->setValue($path, []);
        }
        else {
          if (isset($values[0]['value'])) {
            $values[0]['caption'] = $values[0]['value']['caption'];
            $form_state->setValue($path, $values);
          }
        }
      }
    }

    parent::extractFormValues($items, $form, $form_state);
  }

}
