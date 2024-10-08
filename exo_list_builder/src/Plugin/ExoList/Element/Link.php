<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Element;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Link as CoreLink;
use Drupal\Core\Url;
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
  protected function viewItem(EntityInterface $entity, FieldItemInterface $field_item, array $field) {
    return CoreLink::fromTextAndUrl($field_item->title, Url::fromUri($field_item->uri, $field_item->options))->toString();
  }

}
