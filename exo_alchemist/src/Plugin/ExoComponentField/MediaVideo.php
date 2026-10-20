<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldImageStylesTrait;
use Drupal\exo_icon\ExoIconTranslationTrait;
use Drupal\file\FileInterface;
use Drupal\media\MediaInterface;

/**
 * A 'media' adapter for exo components.
 *
 * @ExoComponentField(
 *   id = "media_video",
 *   label = @Translation("Media: Video"),
 *   properties = {
 *     "url" = @Translation("The absolute url of the video."),
 *     "title" = @Translation("The title of the video."),
 *   },
 *   provider = "media",
 * )
 */
class MediaVideo extends MediaFileBase {
  use ExoIconTranslationTrait;
  use ExoComponentFieldImageStylesTrait;

  /**
   * The default field name holding the video's poster image.
   */
  const POSTER_FIELD = 'field_media_poster';

  /**
   * Get the entity type.
   */
  protected function getEntityTypeBundles() {
    return ['video' => 'video'];
  }

  /**
   * Get the name of the field holding the poster image.
   *
   * @return string
   *   The field name. Can be overridden per-component with [poster_field].
   */
  protected function getPosterFieldName() {
    return $this->getFieldDefinition()->getAdditionalValue('poster_field') ?: static::POSTER_FIELD;
  }

  /**
   * Get the poster image file of a video media entity, if it has one.
   *
   * @param \Drupal\media\MediaInterface $media
   *   The video media entity.
   *
   * @return \Drupal\file\FileInterface|null
   *   The poster file, or NULL when the bundle has no poster field, the field
   *   is empty, or the referenced file has gone missing.
   */
  protected function getPosterFile(MediaInterface $media) {
    $field_name = $this->getPosterFieldName();
    if (!$field_name || !$media->hasField($field_name) || $media->get($field_name)->isEmpty()) {
      return NULL;
    }
    $file = $media->get($field_name)->entity;
    return $file instanceof FileInterface ? $file : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function processDefinition() {
    parent::processDefinition();
    $this->processDefinitionImageStyles($this->getFieldDefinition());
  }

  /**
   * {@inheritdoc}
   */
  public function onInstall(ConfigEntityInterface $entity) {
    parent::onInstall($entity);
    $this->buildImageStyles($this->getFieldDefinition());
  }

  /**
   * {@inheritdoc}
   */
  public function onUpdate(ConfigEntityInterface $entity) {
    parent::onUpdate($entity);
    $this->buildImageStyles($this->getFieldDefinition());
  }

  /**
   * {@inheritdoc}
   */
  public function propertyInfo() {
    $properties = parent::propertyInfo();
    $field = $this->getFieldDefinition();
    $properties['poster.url'] = $this->t('The url of the poster image.');
    if ($field->getAdditionalValue('style_generate') !== FALSE) {
      foreach ($this->propertyInfoImageStyles($field) as $key => $property) {
        $properties['poster.style.' . $key] = $property;
      }
    }
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  protected function viewFileValue(MediaInterface $media, FileInterface $file) {
    $field = $this->getFieldDefinition();
    $field_name = $media->getSource()->getSourceFieldDefinition($media->bundle->entity)->getName();
    $value = [
      'render' => $media->get($field_name)->view([
        'type' => 'file_video',
        'label' => 'hidden',
        'settings' => [
          'controls' => TRUE,
          'autoplay' => FALSE,
          'loop' => FALSE,
          'multiple_file_display_type' => 'tags',
        ],
      ]),
    ] + parent::viewFileValue($media, $file);
    if ($poster = $this->getPosterFile($media)) {
      $value['poster'] = ['url' => $poster->createFileUrl()];
      if ($field->getAdditionalValue('style_generate') !== FALSE) {
        $value['poster']['style'] = $this->getImageStylesAsUrl($field, $poster);
      }
    }
    return $value;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultValue($delta = 0) {
    return [
      'name' => 'Example Video',
      'path' => \Drupal::service('extension.list.module')->getPath('exo_alchemist') . '/video/default.mp4',
    ];
  }

}
