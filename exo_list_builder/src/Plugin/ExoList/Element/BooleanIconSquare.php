<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Element;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\exo_icon\ExoIconTranslationTrait;
use Drupal\exo_list_builder\Plugin\ExoListElementContentBase;

/**
 * Defines a eXo list element for rendering a content entity field.
 *
 * @ExoListElement(
 *   id = "boolean_icon_square",
 *   label = @Translation("Icon: Square"),
 *   description = @Translation("Render the boolean as a square icon."),
 *   weight = 0,
 *   field_type = {
 *     "boolean",
 *   },
 *   entity_type = {},
 *   bundle = {},
 *   field_name = {},
 *   exclusive = FALSE,
 * )
 */
class BooleanIconSquare extends ExoListElementContentBase {
  use ExoIconTranslationTrait;

  /**
   * {@inheritdoc}
   */
  protected function viewItem(EntityInterface $entity, FieldItemInterface $field_item, array $field) {
    $settings = $field['definition']->getSettings();
    return !empty($field_item->value) ?
      $this->icon($settings['on_label'])->setIcon('regular-check-square')->setIconOnly() :
      $this->icon($settings['off_label'])->setIcon('regular-square')->setIconOnly();
  }

}
