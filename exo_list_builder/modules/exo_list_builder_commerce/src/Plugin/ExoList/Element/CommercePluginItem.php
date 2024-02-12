<?php

namespace Drupal\exo_list_builder_commerce\Plugin\ExoList\Element;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\exo_list_builder\Plugin\ExoListElementContentBase;

/**
 * Defines a eXo list element for rendering a content entity field.
 *
 * @ExoListElement(
 *   id = "commerce_plugin_item",
 *   label = @Translation("Commerce Plugin Item"),
 *   description = @Translation("Render the plugin item."),
 *   weight = 0,
 *   field_type = {},
 *   entity_type = {},
 *   bundle = {},
 *   field_name = {},
 *   exclusive = FALSE,
 *   provider = "commerce",
 *   deriver = "\Drupal\exo_list_builder_commerce\Plugin\Derivative\CommercePluginItemDeriver"
 * )
 */
class CommercePluginItem extends ExoListElementContentBase {

  /**
   * {@inheritdoc}
   */
  protected function viewItem(EntityInterface $entity, FieldItemInterface $field_item, array $field) {
    /** @var \Drupal\commerce\Plugin\Field\FieldType\PluginItem $field_item */
    return $field_item->getTargetDefinition()['label'];
  }

}
