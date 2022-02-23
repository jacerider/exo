<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldImageStylesTrait;
use Drupal\file\FileInterface;
use Drupal\media\MediaInterface;

/**
 * A 'media' adapter for exo components.
 *
 * @ExoComponentField(
 *   id = "media_image",
 *   label = @Translation("Media: Image"),
 *   properties = {
 *     "url" = @Translation("The absolute url of the image."),
 *     "title" = @Translation("The title of the image."),
 *   },
 *   provider = "media",
 * )
 */
class MediaImage extends MediaFileBase {

  use ExoComponentFieldImageStylesTrait;

  /**
   * Get the entity type.
   */
  protected function getEntityTypeBundles() {
    $bundles = $this->getFieldDefinition()->getAdditionalValue('bundles');
    if (empty($bundles)) {
      $bundles = $this->getFieldDefinition()->getAdditionalValue('bundle');
    }
    if (empty($bundles)) {
      return ['image' => 'image'];
    }
    return is_array($bundles) ? $bundles : [$bundles => $bundles];
  }

  /**
   * {@inheritdoc}
   */
  public function processDefinition() {
    parent::processDefinition();
    $field = $this->getFieldDefinition();
    $this->processDefinitionImageStyles($field);
  }

  /**
   * {@inheritdoc}
   */
  public function propertyInfo() {
    $properties = parent::propertyInfo();
    $field = $this->getFieldDefinition();
    if ($field->getAdditionalValue('style_generate') !== FALSE) {
      foreach ($this->propertyInfoImageStyles($field) as $key => $property) {
        $properties['style.' . $key] = $property;
      }
    }
    if ($this->moduleHandler()->moduleExists('exo_imagine')) {
      $properties['imagine'] = $this->t('Renderable eXo Imagine image.');
    }
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function onInstall(ConfigEntityInterface $entity) {
    parent::onInstall($entity);
    $field = $this->getFieldDefinition();
    $this->buildImageStyles($field);
  }

  /**
   * {@inheritdoc}
   */
  public function onUpdate(ConfigEntityInterface $entity) {
    parent::onUpdate($entity);
    $field = $this->getFieldDefinition();
    $this->buildImageStyles($field);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultValue($delta = 0) {
    return [
      'path' => drupal_get_path('module', 'exo_alchemist') . '/images/default.png',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function viewValue(FieldItemInterface $item, $delta, array $contexts) {
    $value = parent::viewValue($item, $delta, $contexts);
    if ($this->moduleHandler()->moduleExists('exo_imagine')) {
      /** @var \Drupal\media\MediaInterface $media */
      $media = $item->entity;
      if ($media instanceof MediaInterface) {
        $source_field_definition = $media->getSource()->getSourceFieldDefinition($media->bundle->entity);
        /** @var \Drupal\Core\Field\FieldItemInterface $file_item */
        $file_item = $media->{$source_field_definition->getName()};
        if ($file_item) {
          $field = $this->getFieldDefinition();
          $settings = [];
          foreach ($field->getAdditionalValue('styles') as $breakpoint => $data) {
            $settings['breakpoints'][$breakpoint] = [
              'width' => $data['width'] ?? NULL,
              'height' => $data['height'] ?? NULL,
            ];
          }
          $value['imagine'] = $file_item->view([
            'type' => 'exo_imagine',
            'label' => 'hidden',
            'settings' => $settings,
          ]);
        }
      }
    }
    return $value;
  }

  /**
   * {@inheritdoc}
   */
  protected function viewFileValue(MediaInterface $media, FileInterface $file) {
    $field = $this->getFieldDefinition();
    $value = parent::viewFileValue($media, $file);
    if ($field->getAdditionalValue('style_generate') !== FALSE) {
      $value['style'] = $this->getImageStylesAsUrl($field, $file);
    }
    return $value;
  }

}
