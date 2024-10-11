<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Element;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link as CoreLink;
use Drupal\Core\Url;
use Drupal\exo_list_builder\EntityListInterface;
use Drupal\exo_list_builder\Plugin\ExoListElementContentBase;

/**
 * Defines a eXo list element for rendering a content entity field.
 *
 * @ExoListElement(
 *   id = "link",
 *   label = @Translation("Link"),
 *   description = @Translation("Render the link field as a link."),
 *   weight = 0,
 *   field_type = {
 *     "link",
 *   },
 *   entity_type = {},
 *   bundle = {},
 *   field_name = {},
 *   exclusive = FALSE,
 * )
 */
class Link extends ExoListElementContentBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'link_label' => FALSE,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, EntityListInterface $entity_list, array $field) {
    $configuration = $this->getConfiguration();
    $form['link_label'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Change link title to entity label'),
      '#default_value' => $configuration['link_label'],
    ];
    $form = parent::buildConfigurationForm($form, $form_state, $entity_list, $field);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function viewItem(EntityInterface $entity, FieldItemInterface $field_item, array $field) {
    $title = $field_item->title;
    if ($this->getConfiguration()['link_label']) {
      $title = $entity->label();
    }
    return CoreLink::fromTextAndUrl($title, Url::fromUri($field_item->uri, $field_item->options))->toString();
  }

}
