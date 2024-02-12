<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Element;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\exo_list_builder\EntityListInterface;
use Drupal\exo_list_builder\Plugin\ExoListElementBase;

/**
 * Defines a eXo list element for rendering a content entity field.
 *
 * @ExoListElement(
 *   id = "entity_label",
 *   label = @Translation("Render"),
 *   description = @Translation("Render the entity label and id."),
 *   weight = 0,
 *   field_type = {},
 *   entity_type = {},
 *   bundle = {},
 *   field_name = {
 *    "_label",
 *   },
 *   exclusive = FALSE,
 * )
 */
class EntityLabel extends ExoListElementBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'entity_icon' => TRUE,
      'entity_id' => TRUE,
      'override_label' => '',
      'link_label' => TRUE,
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
    $form['link_label'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Link to the entity'),
      '#default_value' => $configuration['link_label'],
    ];
    $form = parent::buildConfigurationForm($form, $form_state, $entity_list, $field);
    unset($form['link']);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function view(EntityInterface $entity, array $field) {
    $configuration = $this->getConfiguration();
    $label = $configuration['override_label'] ?: ($entity->label() ?: $entity->id());
    $icon = '';
    if ($configuration['link_label'] && ($link = $this->getEntityAsLink($label, $entity))) {
      $label = $link;
    }
    if ($configuration['entity_icon']) {
      $as_icon = $this->getIcon($entity);
      if ($as_icon && $as_icon->hasIcon()) {
        $icon = $as_icon->setIconOnly();
      }
    }
    $string = '<strong>' . $icon . $label . '</strong>';
    if ($configuration['entity_id']) {
      $string .= ' <small>(' . $entity->id() . ')</small>';
    }
    return $string;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityAsLink($label, EntityInterface $entity) {
    if ($entity->getEntityType()->hasLinkTemplate('canonical') && $entity->access('view')) {
      try {
        return Link::fromTextAndUrl($label, $entity->toUrl('canonical'))->toString();
      }
      catch (\Exception $e) {
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function viewPlain(EntityInterface $entity, array $field) {
    return $entity->label() . ' (' . $entity->id() . ')';
  }

}
