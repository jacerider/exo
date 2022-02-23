<?php

namespace Drupal\exo_alchemist;

/**
 * Defines an interface for Component Field plugins.
 */
interface ExoComponentContextInterface {

  /**
   * Check if layout builder.
   *
   * @param \Drupal\Core\Plugin\Context\Context[] $contexts
   *   An array of current contexts.
   *
   * @return bool
   *   TRUE if layout builder.
   */
  public function isLayoutBuilder(array $contexts);

  /**
   * Check if preview.
   *
   * @param \Drupal\Core\Plugin\Context\Context[] $contexts
   *   An array of current contexts.
   *
   * @return bool
   *   TRUE if preview.
   */
  public function isPreview(array $contexts);

  /**
   * Check if locked.
   *
   * @param \Drupal\Core\Plugin\Context\Context[] $contexts
   *   An array of current contexts.
   *
   * @return bool
   *   TRUE if locked.
   */
  public function isLocked(array $contexts);

  /**
   * Check if default storage.
   *
   * @param \Drupal\Core\Plugin\Context\Context[] $contexts
   *   An array of current contexts.
   *
   * @return bool
   *   TRUE if default storage.
   */
  public function isDefaultStorage(array $contexts);

  /**
   * Check if is nested storage.
   *
   * @param \Drupal\Core\Plugin\Context\Context[] $contexts
   *   An array of current contexts.
   *
   * @return bool
   *   TRUE if default storage.
   */
  public function isNestedStorage(array $contexts);

  /**
   * Get entity from context.
   *
   * @param \Drupal\Core\Plugin\Context\Context[] $contexts
   *   An array of current contexts.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The entity.
   */
  public function getEntityFromContexts(array $contexts);

  /**
   * Add cacheable dependency.
   *
   * @param \Drupal\Core\Plugin\Context\Context[] $contexts
   *   An array of current contexts.
   * @param mixed $dependency
   *   The dependency.
   */
  public function addCacheableDependency(array $contexts, $dependency);

  /**
   * Get cachable metadata.
   *
   * @param \Drupal\Core\Plugin\Context\Context[] $contexts
   *   An array of current contexts.
   *
   * @return \Drupal\Core\Cache\CacheableMetadata
   *   The cacheable metadata.
   */
  public function getCacheableMetadata(array $contexts);

}
