<?php

namespace Drupal\exo_media\Plugin\Field\FieldFormatter;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\TypedData\TranslatableInterface;

/**
 * Provides a base formatter for accessing media entities.
 */
trait ExoMediaFormatterTrait {

  /**
   * An array of values keyed by media bundle.
   *
   * @var array
   */
  protected $mediaOtherValues = [];

  /**
   * The collected media entities.
   *
   * @var \Drupal\media\MediaInterface[]
   */
  protected $mediaEntities = [];

  /**
   * {@inheritdoc}
   *
   * This has to be overriden because FileFormatterBase expects $item to be
   * of type \Drupal\file\Plugin\Field\FieldType\FileItem and calls
   * isDisplayed() which is not in FieldItemInterface.
   */
  protected function mediaNeedsEntityLoad(EntityReferenceItem $item) {
    return !$item->hasNewEntity();
  }

  /**
   * {@inheritdoc}
   */
  protected function mediaGetEntitiesToView(EntityReferenceFieldItemListInterface $items, $langcode, $use_thumbnail = TRUE) {
    $entities = [];

    foreach ($items as $delta => $item) {
      // Ignore items where no entity could be loaded in prepareView().
      if (!empty($item->_loaded)) {
        $media_entity = $item->entity;
        $this->mediaEntities[] = $media_entity;
        $source_field = $media_entity->getSource()->getConfiguration()['source_field'];
        $entity = $media_entity->{$source_field}->entity;

        if (!$entity && $use_thumbnail && isset($media_entity->thumbnail->entity)) {
          $source_field = 'thumbnail';
          $entity = $media_entity->{$source_field}->entity;
        }

        // Set the entity in the correct language for display.
        if ($entity instanceof TranslatableInterface) {
          $entity = \Drupal::service('entity.repository')->getTranslationFromContext($entity, $langcode);
        }

        if ($entity instanceof EntityInterface) {
          $access = $this->checkAccess($entity);
          // Add the access result's cacheability, ::view() needs it.
          $item->_accessCacheability = CacheableMetadata::createFromObject($access);
          if ($access->isAllowed()) {
            // Add the referring item, in case the formatter needs it.
            $entity->_referringItem = $media_entity->{$source_field}->get(0);
            $entity->_label = $media_entity->label();
            $entities[$delta] = $entity;
          }
        }
        else {
          // We assume we have something other than a file.
          $this->mediaOtherValues[$delta] = $media_entity->getSource()->getSourceFieldValue($media_entity);
        }
      }
    }

    return $entities;
  }

  /**
   * {@inheritdoc}
   */
  public static function mediaIsApplicable(FieldDefinitionInterface $field_definition) {
    $target_type = $field_definition->getFieldStorageDefinition()->getSetting('target_type');
    return $target_type === 'media';
  }

}
