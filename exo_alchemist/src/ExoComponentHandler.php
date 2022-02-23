<?php

namespace Drupal\exo_alchemist;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\exo_alchemist\Definition\ExoComponentDefinition;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldInterface;

/**
 * Base class for component handlers.
 */
class ExoComponentHandler implements ExoComponentHandlerInterface {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function propertyInfoAlter(array &$info) {}

  /**
   * {@inheritdoc}
   */
  public function fieldPreViewAlter(ExoComponentFieldInterface $field, ContentEntityInterface $entity, array $contexts) {
    $method = 'fieldPreViewAlter' . str_replace('_', '', ucwords($field->getFieldDefinition()->getName(), '_'));
    if (method_exists($this, $method)) {
      $this->{$method}($field, $entity, $contexts);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function viewAlter(array &$values, ExoComponentDefinition $definition, ContentEntityInterface $entity, array $contexts) {}

  /**
   * {@inheritdoc}
   */
  public function viewFirstPreRender(array &$build, ExoComponentDefinition $definition, ContentEntityInterface $entity) {}

  /**
   * {@inheritdoc}
   */
  public function viewLastPreRender(array &$build, ExoComponentDefinition $definition, ContentEntityInterface $entity) {}

}
