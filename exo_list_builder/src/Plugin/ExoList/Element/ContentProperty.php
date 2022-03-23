<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Element;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_list_builder\EntityListInterface;
use Drupal\exo_list_builder\Plugin\ExoListElementContentBase;

/**
 * Defines a eXo list element for rendering a content entity field.
 *
 * @ExoListElement(
 *   id = "content_property",
 *   label = @Translation("Property"),
 *   description = @Translation("Content entity property"),
 *   weight = 0,
 *   field_type = {},
 *   entity_type = {},
 *   bundle = {},
 *   field_name = {},
 *   exclusive = FALSE,
 * )
 */
class ContentProperty extends ExoListElementContentBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'property' => [],
      'prefix' => '',
      'suffix' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, EntityListInterface $entity_list, array $field) {
    $configuration = $this->getConfiguration();
    $property = $this->getPropertyOptions($field['definition']);
    if (empty($configuration['property'])) {
      $configuration['property'] = key($property);
    }
    $form['property'] = [
      '#type' => 'radios',
      '#title' => $this->t('Property'),
      '#options' => $property,
      '#default_value' => $configuration['property'],
      '#required' => TRUE,
    ];
    $form['prefix'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Prefix'),
      '#default_value' => $configuration['prefix'],
    ];
    $form['suffix'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Suffix'),
      '#default_value' => $configuration['suffix'],
    ];
    return parent::buildConfigurationForm($form, $form_state, $entity_list, $field);
  }

  /**
   * {@inheritdoc}
   */
  protected function viewItem(EntityInterface $entity, FieldItemInterface $field_item, array $field) {
    $configuration = $this->getConfiguration();
    $value = $field_item->get($configuration['property'])->getValue();
    return $configuration['prefix'] . $value . $configuration['suffix'];
  }

}
