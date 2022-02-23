<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Element;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\exo_list_builder\EntityListInterface;
use Drupal\exo_list_builder\Plugin\ExoListContentTrait;
use Drupal\exo_list_builder\Plugin\ExoListElementContentBase;

/**
 * Defines a eXo list element for rendering a content entity field.
 *
 * @ExoListElement(
 *   id = "entity_reference_label",
 *   label = @Translation("Label"),
 *   description = @Translation("Render the entity reference as a label."),
 *   weight = 0,
 *   field_type = {
 *    "entity_reference",
 *   },
 *   entity_type = {},
 *   bundle = {},
 *   field_name = {},
 *   exclusive = FALSE,
 * )
 */
class EntityReferenceLabel extends ExoListElementContentBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'link_reference' => FALSE,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, EntityListInterface $entity_list, array $field) {
    $configuration = $this->getConfiguration();
    $form['link_reference'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Link to the referenced entity'),
      '#default_value' => $configuration['link_reference'],
    ];
    $form = parent::buildConfigurationForm($form, $form_state, $entity_list, $field);
    unset($form['link']);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function viewItem(EntityInterface $entity, FieldItemInterface $field_item, array $field) {
    /** @var \Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem $field_item */
    $label = $field_item->entity->label();
    if ($this->getConfiguration()['link_reference']) {
      $label = Link::fromTextAndUrl($label, $field_item->entity->toUrl())->toString();
    }
    return $label;
  }

}
