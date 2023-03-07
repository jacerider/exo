<?php

namespace Drupal\exo_list_builder\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\exo_list_builder\EntityListInterface;

/**
 * Defines an interface for exo list sort.
 */
interface ExoListSortInterface extends PluginInspectionInterface {

  /**
   * Get label.
   *
   * @return string
   *   The label.
   */
  public function label();

  /**
   * Supports direction change.
   *
   * @return bool
   *   TRUE if this sort supports direction change.
   */
  public function supportsDirectionChange();

  /**
   * Get the default direction.
   *
   * @return string
   *   The default direction.
   */
  public function getDefaultDirection();

  /**
   * Get asc label.
   *
   * @return string
   *   The label.
   */
  public function getAscLabel();

  /**
   * Get desc label.
   *
   * @return string
   *   The label.
   */
  public function getDescLabel();

  /**
   * Do sort.
   *
   * @param \Drupal\Core\Entity\Query\QueryInterface|\Drupal\Core\Entity\Query\ConditionInterface $query
   *   The query.
   * @param \Drupal\exo_list_builder\EntityListInterface $entity_list
   *   The entity list.
   * @param string $direction
   *   Either ASC or DESC.
   */
  public function sort($query, EntityListInterface $entity_list, &$direction = NULL);

  /**
   * Whether this theme negotiator should be used on the current list.
   *
   * @param \Drupal\exo_list_builder\EntityListInterface $exo_list
   *   The exo list builder.
   *
   * @return bool
   *   TRUE if this filter should be allowed.
   */
  public function applies(EntityListInterface $exo_list);

}
