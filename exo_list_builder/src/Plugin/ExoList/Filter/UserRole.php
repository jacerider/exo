<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Filter;

use Drupal\exo_list_builder\EntityListInterface;

/**
 * Defines a eXo list element for rendering a content entity field.
 *
 * @ExoListFilter(
 *   id = "user_role",
 *   label = @Translation("Select"),
 *   description = @Translation("Filter by the user role."),
 *   weight = 0,
 *   field_type = {},
 *   entity_type = {},
 *   bundle = {},
 *   field_name = {
 *     "roles",
 *   },
 *   exclusive = FALSE,
 * )
 */
class UserRole extends OptionsSelect {

  /**
   * {@inheritdoc}
   */
  public function getValueOptions(EntityListInterface $entity_list, array $field, $input = NULL) {
    $options = [];
    foreach ($this->entityTypeManager()->getStorage('user_role')->loadMultiple() as $role) {
      $options[$role->id()] = $role->label();
    }
    return $options;
  }

}
