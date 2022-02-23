<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Element;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\exo_list_builder\Plugin\ExoListElementBase;

/**
 * Defines a eXo list element for rendering a content entity field.
 *
 * @ExoListElement(
 *   id = "status_icon",
 *   label = @Translation("Status Icon"),
 *   description = @Translation("Render the status icon."),
 *   weight = 0,
 *   field_type = {
 *     "config",
 *     "boolean",
 *   },
 *   entity_type = {},
 *   bundle = {},
 *   field_name = {},
 *   exclusive = FALSE,
 * )
 */
class StatusIcon extends ExoListElementBase {

  /**
   * {@inheritdoc}
   */
  protected function view(EntityInterface $entity, array $field) {
    if ($entity instanceof ContentEntityInterface) {
      $published = !empty($entity->get($field['id'])->value);
      if ($entity instanceof EntityPublishedInterface) {
        $published = $entity->isPublished();
      }
      return $published ?
        exo_icon('Published')->setIcon('regular-toggle-on')->setIconOnly() :
        exo_icon('Unpublished')->setIcon('regular-toggle-off')->setIconOnly();
    }
    elseif ($entity instanceof ConfigEntityInterface) {
      return $entity->get('status') ?
        exo_icon('Published')->setIcon('regular-toggle-on')->setIconOnly() :
        exo_icon('Unpublished')->setIcon('regular-toggle-off')->setIconOnly();
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function viewPlain(EntityInterface $entity, array $field) {
    if ($entity instanceof ContentEntityInterface) {
      $published = !empty($entity->get($field['id'])->value);
      if ($entity instanceof EntityPublishedInterface) {
        $published = $entity->isPublished();
      }
      return $published ? $this->t('Published') : $this->t('Unpublished');
    }
    elseif ($entity instanceof ConfigEntityInterface) {
      return $entity->get('status') ? $this->t('Published') : $this->t('Unpublished');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function applies(array $field) {
    /** @var \Drupal\Core\Field\FieldDefinitionInterface $field_definition */
    $field_definition = $field['definition'];
    $entity_type_id = $field_definition->getTargetEntityTypeId();
    $entity_type = $this->entityTypeManager()->getDefinition($entity_type_id);

    if (!is_subclass_of($entity_type->getClass(), EntityPublishedInterface::class)) {
      return FALSE;
    }
    if (!$entity_type->hasKey('published')) {
      return FALSE;
    }
    return $entity_type->getKey('published') === $field['id'];
  }

}
