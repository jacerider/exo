<?php

namespace Drupal\exo_alchemist;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\exo_alchemist\Definition\ExoComponentDefinition;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldInterface;

/**
 * Interface for component handlers.
 */
interface ExoComponentHandlerInterface {

  /**
   * Alter component property information.
   *
   * @param array $info
   *   An array of property info.
   */
  public function propertyInfoAlter(array &$info);

  /**
   * Alter component field before it is processed for viewing.
   *
   * @param \Drupal\exo_alchemist\Plugin\ExoComponentFieldInterface $field
   *   The component definition.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The content entity.
   * @param \Drupal\Core\Plugin\Context\Context[] $contexts
   *   An array of current contexts.
   */
  public function fieldPreViewAlter(ExoComponentFieldInterface $field, ContentEntityInterface $entity, array $contexts);

  /**
   * Alter component before it is sent to renderer.
   *
   * @param array $values
   *   The values sent to component.
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition
   *   The component definition.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The content entity.
   * @param \Drupal\Core\Plugin\Context\Context[] $contexts
   *   An array of current contexts.
   */
  public function viewAlter(array &$values, ExoComponentDefinition $definition, ContentEntityInterface $entity, array $contexts);

  /**
   * Alter first component before it is sent to renderer.
   *
   * @param array $build
   *   The render array for the component.
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition
   *   The component definition.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The content entity.
   */
  public function viewFirstPreRender(array &$build, ExoComponentDefinition $definition, ContentEntityInterface $entity);

  /**
   * Alter last component before it is sent to renderer.
   *
   * @param array $build
   *   The render array for the component.
   * @param \Drupal\exo_alchemist\Definition\ExoComponentDefinition $definition
   *   The component definition.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The content entity.
   */
  public function viewLastPreRender(array &$build, ExoComponentDefinition $definition, ContentEntityInterface $entity);

}
