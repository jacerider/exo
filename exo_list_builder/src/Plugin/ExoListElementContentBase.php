<?php

namespace Drupal\exo_list_builder\Plugin;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_list_builder\EntityListInterface;

/**
 * Base class for eXo list elements.
 */
abstract class ExoListElementContentBase extends ExoListElementBase {
  use ExoListContentTrait;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'display_mode' => 'all',
      'display_amount' => 1,
      'display_offset' => 0,
      'display_reverse' => FALSE,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, EntityListInterface $entity_list, array $field) {
    $form = parent::buildConfigurationForm($form, $form_state, $entity_list, $field);
    $cardinality = !empty($field['definition']) ? $field['definition']->getFieldStorageDefinition()->getCardinality() : 1;
    if ($cardinality !== 1) {
      $id = $form['#id'] . '-display';
      $form['display_mode'] = [
        '#type' => 'select',
        '#options' => $this->getDisplayModes(),
        '#title' => t('Display mode'),
        '#default_value' => $this->configuration['display_mode'],
        '#required' => TRUE,
        '#id' => $id,
      ];

      $show_advanced = [
        'visible' => [
          '#' . $id => [
            'value' => 'advanced',
          ],
        ],
      ];

      $form['display_amount'] = [
        '#type' => 'number',
        '#step' => 1,
        '#min' => 1,
        '#title' => t('Amount of displayed entities'),
        '#default_value' => $this->configuration['display_amount'],
        '#states' => $show_advanced,
      ];
      if ($cardinality > 0) {
        $form['display_amount']['#max'] = $cardinality;
      }

      $form['display_offset'] = [
        '#type' => 'number',
        '#step' => 1,
        '#min' => 0,
        '#title' => t('Offset'),
        '#default_value' => $this->configuration['display_offset'],
        '#states' => $show_advanced,
        '#cardinality' => $cardinality,
        '#element_validate' => [[get_class($this), 'validateDisplayOffset']],
      ];

      $form['display_reverse'] = [
        '#type' => 'checkbox',
        '#title' => t('Reverse order'),
        '#desctiption' => t('Check this if you want to show the last added entities of the field. For example use amount 2 and "Reverse order" in order to display the last two entities in the field.'),
        '#default_value' => $this->configuration['display_reverse'],
        '#states' => $show_advanced,
      ];
    }
    return $form;
  }

  /**
   * Validation callback for the offset element.
   *
   * @param array $element
   *   The form element to process.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public static function validateDisplayOffset(array &$element, FormStateInterface $form_state) {
    $cardinality = $element['#cardinality'];
    $parents = $element['#parents'];
    array_pop($parents);
    $values = $form_state->getValue($parents);
    if ($values['display_mode'] === 'advanced') {
      $offset_maximum = $cardinality - $values['display_amount'];
      // If cardinality of the field is limited, the offset has to be lower than
      // the field's cardinality minus the submitted amount value.
      if ($cardinality > 0 && $values['display_offset'] > $offset_maximum) {
        $form_state->setError($element, t(
            'The maximal offset for the submitted amount is @offset',
            ['@offset' => $offset_maximum]
          )
        );
      }
    }
  }

  /**
   * Get the formatter's selection mode options.
   *
   * @return array
   *   Array of available selection modes.
   */
  protected function getDisplayModes() {
    return [
      'all' => t('All'),
      'first' => t('First'),
      'last' => t('Last'),
      'advanced' => t('Advanced'),
    ];
  }

  /**
   * Get viewable output.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param array $field
   *   The field definition.
   *
   * @return mixed
   *   A renderable array or string.
   */
  protected function view(EntityInterface $entity, array $field) {
    $configuration = $this->getConfiguration();
    $field_items = $this->getItems($entity, $field);
    if (!$field_items) {
      return $configuration['empty'];
    }
    $field_items = $this->prepareItems($field_items);
    $values = [];
    foreach ($field_items as $field_item) {
      if ($field_item->isEmpty()) {
        return $configuration['empty'];
      }
      $value = $this->viewItem($entity, $field_item, $field);
      if (is_array($value)) {
        $renderer = \Drupal::service('renderer');
        $value = $renderer->render($value);
      }
      $values[] = $value;
    }
    return implode($configuration['separator'], $values) ?: $configuration['empty'];
  }

  /**
   * Get viewable item.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param \Drupal\Core\Field\FieldItemInterface $field_item
   *   The field item.
   * @param array $field
   *   The field definition.
   *
   * @return string
   *   The viewable item.
   */
  protected function viewItem(EntityInterface $entity, FieldItemInterface $field_item, array $field) {
    return '-';
  }

  /**
   * Get plain output.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param array $field
   *   The field definition.
   *
   * @return mixed
   *   A renderable array or string.
   */
  protected function viewPlain(EntityInterface $entity, array $field) {
    $configuration = $this->getConfiguration();
    $field_items = $this->getItems($entity, $field);
    if (!$field_items) {
      return $configuration['empty'];
    }
    $field_items = $this->prepareItems($field_items);
    $configuration = $this->getConfiguration();
    $values = [];
    foreach ($field_items as $field_item) {
      if ($field_item->isEmpty()) {
        return NULL;
      }
      $value = $this->viewPlainItem($entity, $field_item, $field);
      if (is_array($value)) {
        $renderer = \Drupal::service('renderer');
        $value = $renderer->render($value);
      }
      $values[] = $value;
    }
    return implode($configuration['separator'], $values) ?: $configuration['empty'];
  }

  /**
   * Get plain viewable item.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param \Drupal\Core\Field\FieldItemInterface $field_item
   *   The field item.
   * @param array $field
   *   The field definition.
   *
   * @return string
   *   The viewable item.
   */
  protected function viewPlainItem(EntityInterface $entity, FieldItemInterface $field_item, array $field) {
    return $this->viewItem($entity, $field_item, $field);
  }

  /**
   * Prepare items for display.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $field_items
   *   The field item list.
   *
   * @return \Drupal\Core\Field\FieldItemListInterface
   *   The field item list.
   */
  protected function prepareItems(FieldItemListInterface $field_items) {
    $configuration = $this->getConfiguration();
    $mode = $configuration['display_mode'];
    if ($mode !== 'all') {
      /** @var \Drupal\Core\Field\FieldItemListInterface $new_field_items */
      $new_field_items = clone $field_items;
      $amount = (int) $configuration['display_amount'];
      $offset = (int) $configuration['display_offset'];
      $reverse = $configuration['display_reverse'];
      switch ($configuration['display_mode']) {
        case 'first':
          $amount = 1;
          $offset = 0;
          $reverse = FALSE;
          break;

        case 'last':
          $amount = 1;
          $offset = count($new_field_items) - 1;
          $reverse = FALSE;
          break;
      }
      $values = $new_field_items->getValue();
      if ($reverse) {
        $values = array_reverse($values);
      }
      $count = 0;
      $new_values = [];
      foreach ($values as $delta => $value) {
        if ($delta >= $offset && $count < $amount) {
          $new_values[] = $value;
          $count++;
        }
      }
      $new_field_items->setValue($new_values);
      return $new_field_items;
    }
    return $field_items;
  }

}
