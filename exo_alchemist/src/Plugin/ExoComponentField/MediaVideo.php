<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

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

  /**
   * Get the entity type.
   */
  protected function getEntityTypeBundles() {
    return ['video' => 'video'];
  }

  /**
   * {@inheritdoc}
   */
  public function propertyInfo() {
    $properties = parent::propertyInfo();
    // $properties['icon'] = $this->t('The file type icon.');
    // $properties['icon_render'] = $this->t('The rendered file type icon.');
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  protected function viewFileValue(MediaInterface $media, FileInterface $file) {
    $field_name = $media->getSource()->getSourceFieldDefinition($media->bundle->entity)->getName();
    return [
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
