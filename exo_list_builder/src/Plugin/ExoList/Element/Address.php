<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Element;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_list_builder\EntityListInterface;
use Drupal\exo_list_builder\Plugin\ExoListElementContentBase;
use Drupal\address\LabelHelper;

/**
 * Defines a eXo list element for rendering a content entity field.
 *
 * @ExoListElement(
 *   id = "address",
 *   label = @Translation("Address"),
 *   description = @Translation("Render the address."),
 *   weight = 0,
 *   field_type = {
 *     "address",
 *   },
 *   entity_type = {},
 *   bundle = {},
 *   field_name = {},
 *   exclusive = FALSE,
 * )
 */
class Address extends ExoListElementContentBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'hidden' => [],
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, EntityListInterface $entity_list, array $field) {
    $configuration = $this->getConfiguration();
    $form['hidden'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Hidden Elements'),
      '#options' => LabelHelper::getGenericFieldLabels(),
      '#default_value' => $configuration['hidden'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array $form, FormStateInterface $form_state) {
    $form_state->setValue('hidden', array_filter($form_state->getValue('hidden')));
  }

  /**
   * {@inheritdoc}
   */
  protected function viewItem(EntityInterface $entity, FieldItemInterface $field_item, array $field) {
    $configuration = $this->getConfiguration();
    $value = $field_item->getValue();
    foreach ($configuration['hidden'] as $key) {
      $key = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $key));
      $value[$key] = NULL;
    }
    return [
      '#type' => 'inline_template',
      '#template' => '
        {% if given_name or family_name %}
          {{ given_name }} {{ family_name }},
        {% endif %}
        {% if organization %}
          {{ organization }},
        {% endif %}
        {% if address_line1 %}
          {{ address_line1 }},
        {% endif %}
        {% if address_line2 %}
          {{ address_line2 }},
        {% endif %}
        {% if dependent_locality.code %}
          {{ dependent_locality.code }},
        {% endif %}
        {% if locality or administrative_area.code or postal_code %}
          {{ locality }}{% if administrative_area.code %}, {{ administrative_area.code }}{% endif %}{% if postal_code %}, {{ postal_code }}{% endif %}
        {% endif %}
      ',
      '#context' => $value,
    ];
    // return implode(' ', array_filter([
    //   $value['given_name'],
    //   $value['additional_name'],
    //   $value['family_name'],
    // ]));
  }

}
