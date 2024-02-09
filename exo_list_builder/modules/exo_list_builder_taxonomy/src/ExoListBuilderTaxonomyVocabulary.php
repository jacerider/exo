<?php

namespace Drupal\exo_list_builder_taxonomy;

use Drupal\exo_list_builder\ExoListBuilderConfig;

/**
 * Provides a list builder for content entities.
 */
class ExoListBuilderTaxonomyVocabulary extends ExoListBuilderConfig {

  /**
   * {@inheritdoc}
   */
  public function load() {
    $entities = parent::load();
    $user = \Drupal::currentUser();
    foreach ($entities as $key => $entity) {
      if ($user->hasPermission('administer taxonomy')) {
        continue;
      }
      if ($user->hasPermission('edit terms in ' . $entity->id())) {
        continue;
      }
      if ($user->hasPermission('delete terms in ' . $entity->id())) {
        continue;
      }
      if ($user->hasPermission('create terms in ' . $entity->id())) {
        continue;
      }
      unset($entities[$key]);
    }
    return $entities;
  }

}
