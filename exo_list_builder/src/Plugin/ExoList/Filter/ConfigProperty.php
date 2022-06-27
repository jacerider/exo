<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Filter;

use Drupal\exo_list_builder\EntityListInterface;
use Drupal\exo_list_builder\Plugin\ExoListFieldValuesInterface;
use Drupal\exo_list_builder\Plugin\ExoListFilterMatchBase;

/**
 * Defines a eXo list element for rendering a config entity field.
 *
 * @ExoListFilter(
 *   id = "config_property",
 *   label = @Translation("Property"),
 *   description = @Translation("Filter by entity property."),
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
class ConfigProperty extends ExoListFilterMatchBase implements ExoListFieldValuesInterface {

  /**
   * {@inheritdoc}
   */
  public function queryAlter($query, $value, EntityListInterface $entity_list, array $field) {
    $this->queryAlterByField($field['field_name'], $query, $value, $entity_list, $field);
  }

  /**
   * {@inheritdoc}
   */
  public function getValueOptions(EntityListInterface $entity_list, array $field, $input = NULL) {
    /** @var \Drupal\Core\Config\Entity\ConfigEntityInterface[] $entities */
    $entities = \Drupal::entityTypeManager()->getStorage($entity_list->getTargetEntityTypeId())->loadMultiple();
    $options = [];
    foreach ($entities as $entity) {
      $value = $entity->get('target_entity_type');
      if (is_string($value)) {
        $options[$value] = $value;
      }
      if (is_array($value)) {
        foreach ($value as $val) {
          if (is_string($val)) {
            $options[$val] = $val;
          }
        }
      }
    }
    return array_combine($options, $options);
  }

}
