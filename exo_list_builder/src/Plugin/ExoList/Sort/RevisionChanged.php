<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Sort;

use Drupal\exo_list_builder\EntityListInterface;
use Drupal\exo_list_builder\Plugin\ExoListSortBase;

/**
 * Defines a eXo list action for batch operations.
 *
 * @ExoListSort(
 *   id = "revision_changed",
 *   label = @Translation("Pending"),
 *   description = @Translation("Sort by latest revision changed time."),
 *   weight = 0,
 *   entity_type = {},
 *   bundle = {},
 * )
 */
class RevisionChanged extends ExoListSortBase {

  /**
   * {@inheritdoc}
   */
  protected $supportsDirectionChange = TRUE;

  /**
   * {@inheritdoc}
   */
  protected $defaultDirection = 'desc';

  /**
   * {@inheritdoc}
   */
  public function getAscLabel() {
    return $this->label() . ': ' . $this->t('Oldest');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescLabel() {
    return $this->label() . ': ' . $this->t('Newest');
  }

  /**
   * {@inheritdoc}
   */
  public function sort($query, EntityListInterface $entity_list, &$direction = NULL) {
    $do_sort = TRUE;
    if ($moderation_filter_value = $entity_list->getHandler()->getOption([
      'filter',
      'moderation_state',
    ])) {
      $parts = explode(':', $moderation_filter_value);
      if (!isset($parts[1]) || $parts[0] !== 'latest') {
        $do_sort = FALSE;
      }
    }
    if ($do_sort) {
      foreach ($entity_list->getFields() as $field) {
        if ($field['type'] === 'changed') {
          $direction = $direction ?: $this->getDefaultDirection();
          $query->latestRevision();
          $query->addTag('exo_entity_list_moderation_state');
          $query->addMetaData('exo_entity_list_moderation_state_sort_field', $field['field_name']);
          $query->addMetaData('exo_entity_list_moderation_state_sort_direction', $direction);
          return;
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function applies(EntityListInterface $exo_list) {
    $fields = $exo_list->getFields();
    foreach ($fields as $field) {
      if ($field['type'] === 'changed') {
        return isset($fields['moderation_state']);
      }
    }
    return FALSE;
  }

}
