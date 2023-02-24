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
 *   id = "entity_link",
 *   label = @Translation("Render"),
 *   description = @Translation("Render the entity as a link."),
 *   weight = 0,
 *   field_type = {},
 *   entity_type = {},
 *   bundle = {},
 *   field_name = {
 *    "_link",
 *   },
 *   exclusive = FALSE,
 * )
 */
class EntityLink extends ExoListElementBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'entity_icon' => TRUE,
      'link_text' => 'View',
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
    $form['link_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Link text'),
      '#default_value' => $configuration['link_text'],
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
    $label = $configuration['link_text'] ?: $entity->label();
    $icon = '';
    if ($link = $this->getEntityAsLink($label, $entity)) {
      $label = $link;
    }
    if ($configuration['entity_icon']) {
      $as_icon = $this->getIcon($entity);
      if ($as_icon && $as_icon->hasIcon()) {
        $icon = $as_icon->setIconOnly();
      }
    }
    $string = $icon . $label;
    return $string;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityAsLink($label, EntityInterface $entity) {
    if ($entity->getEntityType()->hasLinkTemplate('canonical') && $entity->access('view')) {
      return Link::fromTextAndUrl($label, $entity->toUrl('canonical'))->toString();
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function viewPlain(EntityInterface $entity, array $field) {
    if ($entity->getEntityType()->hasLinkTemplate('canonical') && $entity->access('view')) {
      return $entity->toUrl('canonical')->setAbsolute()->toString();
    }
    return NULL;
  }

}
