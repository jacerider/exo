<?php

namespace Drupal\exo_alchemist;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Manipulates entity type information.
 *
 * This class contains primarily bridged hooks for compile-time or
 * cache-clear-time hooks. Runtime hooks should be placed in EntityOperations.
 *
 * @internal
 */
class EntityTypeInfo {

  /**
   * Adds base field info to an entity type.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   Entity type for adding base fields to.
   *
   * @return \Drupal\Core\Field\BaseFieldDefinition[]
   *   New fields added by moderation state.
   *
   * @see hook_entity_base_field_info()
   */
  public function entityBaseFieldInfo(EntityTypeInterface $entity_type) {
    $fields = [];

    if ($entity_type->id() === ExoComponentManager::ENTITY_TYPE) {
      $fields['alchemist_data'] = BaseFieldDefinition::create('exo_alchemist_map')
        ->setLabel(t('Data'))
        ->setDescription(t('Storage for component configuration.'))
        ->setRevisionable(TRUE);
      $fields['alchemist_path'] = BaseFieldDefinition::create('string')
        ->setLabel(t('Path'))
        ->setDescription(t('Storage for component path.'))
        ->setSetting('max_length', 255);
      $fields['alchemist_default'] = BaseFieldDefinition::create('boolean')
        ->setLabel(t('Default'))
        ->setDescription(t('A flag indicating whether this is the default entity.'))
        ->setDefaultValue(TRUE);
    }

    if ($entity_type->id() === 'media') {
      $fields['alchemist_key'] = BaseFieldDefinition::create('string')
        ->setLabel(t('Key'))
        ->setDescription(t('A key that can be used to prevent duplicate entity creation.'))
        ->setReadOnly(TRUE);
    }

    return $fields;
  }

}
