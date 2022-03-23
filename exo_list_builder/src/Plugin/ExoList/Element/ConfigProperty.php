<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Element;

use Drupal\Core\Entity\EntityInterface;
use Drupal\exo_list_builder\Plugin\ExoListElementBase;

/**
 * Defines a eXo list element for rendering a content entity field.
 *
 * @ExoListElement(
 *   id = "config_property",
 *   label = @Translation("Property"),
 *   description = @Translation("Config entity property"),
 *   weight = 0,
 *   field_type = {
 *     "config",
 *   },
 *   entity_type = {},
 *   bundle = {},
 *   field_name = {},
 *   exclusive = FALSE,
 * )
 */
class ConfigProperty extends ExoListElementBase {

  /**
   * {@inheritdoc}
   */
  protected function view(EntityInterface $entity, array $field) {
    /** @var \Drupal\Core\Config\Entity\ConfigEntityInterface $entity */
    $configuration = $this->getConfiguration();
    $value = $entity->get($field['field_name']);
    if (!is_array($value)) {
      $value = [$value];
    }
    return implode($configuration['separator'], $value) ?: parent::view($entity, $field);
  }

}
