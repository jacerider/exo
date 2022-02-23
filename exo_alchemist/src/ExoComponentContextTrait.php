<?php

namespace Drupal\exo_alchemist;

use Drupal\Core\Cache\CacheableMetadata;

/**
 * The eXo component context trait.
 */
trait ExoComponentContextTrait {

  /**
   * {@inheritdoc}
   */
  public function isLayoutBuilder(array $contexts) {
    return !isset($contexts['entity']) && !$this->isPreview($contexts);
  }

  /**
   * {@inheritdoc}
   */
  public function isPreview(array $contexts) {
    return isset($contexts['preview']) ? $contexts['preview']->getContextValue() : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function isLocked(array $contexts) {
    return isset($contexts['exo_section_lock']) ? $contexts['exo_section_lock']->getContextValue() : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function isDefaultStorage(array $contexts) {
    return isset($contexts['default_storage']) ? $contexts['default_storage']->getContextValue() : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function isNestedStorage(array $contexts) {
    return isset($contexts['nested_storage']) ? $contexts['nested_storage']->getContextValue() : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityFromContexts(array $contexts) {
    if (isset($contexts['entity'])) {
      $entity_context = $contexts['entity'];
    }
    else {
      $entity_context = $contexts['layout_builder.entity'];
    }
    return $entity_context->getContextValue();
  }

  /**
   * {@inheritdoc}
   */
  public function addCacheableDependency(array $contexts, $dependency) {
    if (!empty($dependency)) {
      if (is_array($dependency)) {
        $dependency = CacheableMetadata::createFromRenderArray($dependency);
      }
      $this->getCacheableMetadata($contexts)->addCacheableDependency($dependency);
    }
    return $this;
  }

  /**
   * Get cachable metadata.
   *
   * @param \Drupal\Core\Plugin\Context\Context[] $contexts
   *   An array of current contexts.
   *
   * @return \Drupal\exo_alchemist\Cache\ExoCacheableContext
   *   The cacheable metadata.
   */
  public function getCacheableMetadata(array $contexts) {
    /** @var \Drupal\exo_alchemist\Cache\ExoCacheableContext $cacheable_context */
    return $contexts['cacheable_metadata']->getContextValue();
  }

}
