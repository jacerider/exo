<?php

namespace Drupal\exo_list_builder_redirect\Plugin\ExoList\Element;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\exo_list_builder\Plugin\ExoListElementContentBase;

/**
 * Defines a eXo list element for rendering a content entity field.
 *
 * @ExoListElement(
 *   id = "redirect_source",
 *   label = @Translation("Redirect Source"),
 *   description = @Translation("Render redirect source."),
 *   weight = 0,
 *   field_type = {},
 *   entity_type = {
 *     "redirect",
 *   },
 *   bundle = {},
 *   field_name = {
 *     "redirect_source",
 *   },
 *   exclusive = FALSE,
 * )
 */
class RedirectSource extends ExoListElementContentBase {

  /**
   * Allow field to be linked to entity.
   *
   * @var bool
   */
  protected $allowEntityLink = FALSE;

  /**
   * {@inheritdoc}
   */
  protected function viewItem(EntityInterface $entity, FieldItemInterface $field_item, array $field) {
    /** @var \Drupal\redirect\Plugin\Field\FieldType\RedirectSourceItem $field_item */
    return urldecode($field_item->getUrl()->toString());
  }

}
