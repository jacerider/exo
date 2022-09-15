<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Element;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\exo_list_builder\Plugin\ExoListElementContentBase;

/**
 * Defines a eXo list element for rendering a content entity field.
 *
 * @ExoListElement(
 *   id = "file_image",
 *   label = @Translation("Image"),
 *   description = @Translation("Render the image."),
 *   weight = 0,
 *   field_type = {
 *     "image",
 *   },
 *   entity_type = {},
 *   bundle = {},
 *   field_name = {},
 *   exclusive = FALSE,
 * )
 */
class FileImage extends ExoListElementContentBase {

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return [
      'separator' => '',
    ] + parent::getConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  protected function viewItem(EntityInterface $entity, FieldItemInterface $field_item, array $field) {
    /** @var \Drupal\file\FileInterface $file */
    $file = $field_item->entity;
    return [
      '#theme' => 'image_style',
      // '#width' => $variables['width'],
      // '#height' => $variables['height'],
      '#style_name' => 'exo_list_builder_thumbnail',
      '#uri' => $file->getFileUri(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function viewPlainItem(EntityInterface $entity, FieldItemInterface $field_item, array $field) {
    /** @var \Drupal\file\FileInterface $file */
    $file = $field_item->entity;
    return $file->getFileUri();
  }

}
