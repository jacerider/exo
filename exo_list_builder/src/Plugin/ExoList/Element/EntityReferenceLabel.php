<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Element;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\exo_list_builder\EntityListInterface;
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
 *     "entity_reference",
 *     "entity_reference_revisions",
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
      'entity_icon' => FALSE,
      'entity_id' => TRUE,
      'override_label' => '',
      'link_reference' => FALSE,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, EntityListInterface $entity_list, array $field) {
    $configuration = $this->getConfiguration();
    $form['entity_icon'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show entity icon'),
      '#default_value' => $configuration['entity_icon'],
    ];
    $form['entity_id'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show entity ID'),
      '#default_value' => $configuration['entity_id'],
    ];
    $form['override_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Override label'),
      '#default_value' => $configuration['override_label'],
    ];
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
    $reference_entity = $field_item->entity;
    if (!$reference_entity) {
      return NULL;
    }
    $configuration = $this->getConfiguration();
    $label = $configuration['override_label'] ?: ($reference_entity->label() ?: $reference_entity->id());
    $icon = '';
    if ($configuration['link_reference'] && $reference_entity->getEntityType()->hasLinkTemplate('canonical') && $reference_entity->access('view')) {
      $label = Link::fromTextAndUrl($label, $reference_entity->toUrl('canonical'))->toString();
    }
    if ($configuration['entity_icon']) {
      $as_icon = $this->getIcon($reference_entity);
      if ($as_icon->hasIcon()) {
        $icon = $as_icon->setIconOnly();
      }
    }
    $string = $icon . $label;
    if ($configuration['entity_id']) {
      $string .= ' <em>(' . $reference_entity->id() . ')</em>';
    }
    return $string;
  }

  /**
   * {@inheritdoc}
   */
  protected function viewPlainItem(EntityInterface $entity, FieldItemInterface $field_item, array $field) {
    /** @var \Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem $field_item */
    $reference_entity = $field_item->entity;
    $configuration = $this->getConfiguration();
    $label = $reference_entity->label();
    if ($configuration['entity_id']) {
      $label .= ' (' . $reference_entity->id() . ')';
    }
    return $label;
  }

}
