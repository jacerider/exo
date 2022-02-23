<?php

namespace Drupal\exo_alchemist\Cache;

use Drupal\Core\Cache\CacheableMetadata;

/**
 * Provides an implementation of CacheableResponseInterface.
 *
 * @see \Drupal\Core\Cache\CacheableResponseInterface
 */
class ExoCacheableContext {

  /**
   * The cacheability metadata.
   *
   * @var \Drupal\Core\Cache\CacheableMetadata
   */
  protected $cacheabilityMetadata;

  /**
   * Adds a dependency on an object: merges its cacheability metadata.
   *
   * For instance, when a response depends on some configuration, an entity, or
   * an access result, we must make sure their cacheability metadata is present
   * on the response. This method makes doing that simple.
   *
   * @param \Drupal\Core\Cache\CacheableDependencyInterface|mixed $dependency
   *   The dependency. If the object implements CacheableDependencyInterface,
   *   then its cacheability metadata will be used. Otherwise, the passed in
   *   object must be assumed to be uncacheable, so max-age 0 is set.
   *
   * @return $this
   *
   * @see \Drupal\Core\Cache\CacheableMetadata::createFromObject()
   */
  public function addCacheableDependency($dependency) {
    // A trait doesn't have a constructor, so initialize the cacheability
    // metadata if that hasn't happened yet.
    if (!isset($this->cacheabilityMetadata)) {
      $this->cacheabilityMetadata = new CacheableMetadata();
    }
    if (is_array($dependency)) {
      $cacheable_dependency = CacheableMetadata::createFromRenderArray($dependency);
    }
    else {
      $cacheable_dependency = CacheableMetadata::createFromObject($dependency);
    }

    $this->cacheabilityMetadata = $this->cacheabilityMetadata->merge($cacheable_dependency);

    return $this;
  }

  /**
   * Returns the cacheability metadata for this response.
   *
   * @return \Drupal\Core\Cache\CacheableMetadata
   *   The cacheable metadata.
   */
  public function getCacheableMetadata() {
    // A trait doesn't have a constructor, so initialize the cacheability
    // metadata if that hasn't happened yet.
    if (!isset($this->cacheabilityMetadata)) {
      $this->cacheabilityMetadata = new CacheableMetadata();
    }

    return $this->cacheabilityMetadata;
  }

  /**
   * Sets the cacheable metadata.
   *
   * @param Drupal\Core\Cache\CacheableMetadata $cacheable_metadata
   *   The cacheable metadata.
   *
   * @return $this
   */
  public function setCacheableMetadata(CacheableMetadata $cacheable_metadata) {
    $this->cacheabilityMetadata = $cacheable_metadata;
    return $this;
  }

}
