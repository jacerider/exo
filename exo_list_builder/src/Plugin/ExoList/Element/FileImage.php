<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Element;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_list_builder\EntityListInterface;
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
  public function defaultConfiguration() {
    return [
      'image_style' => 'exo_list_builder_thumbnail',
    ] + parent::defaultConfiguration();
  }

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
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, EntityListInterface $entity_list, array $field) {
    $form = parent::buildConfigurationForm($form, $form_state, $entity_list, $field);
    $configuration = $this->getConfiguration();
    $image_styles = image_style_options(FALSE);
    $form['image_style'] = [
      '#title' => $this->t('Image style'),
      '#type' => 'select',
      '#default_value' => $configuration['image_style'],
      '#empty_option' => $this->t('None (original image)'),
      '#options' => $image_styles,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function viewItem(EntityInterface $entity, FieldItemInterface $field_item, array $field) {
    $build = [];
    /** @var \Drupal\file\FileInterface $file */
    $file = $field_item->entity;
    if (!$file) {
      return $build;
    }
    /** @var \Drupal\Core\Image\ImageFactory $image_factory */
    $image_factory = \Drupal::service('image.factory');
    $image = $image_factory->get($file->getFileUri());
    if ($image->isValid()) {
      $configuration = $this->getConfiguration();
      $cache_tags = [];
      $image_style = NULL;
      if (!empty($configuration['image_style'])) {
        $image_style = $configuration['image_style'];
        $image_style_storage = \Drupal::entityTypeManager()->getStorage('image_style')->load($image_style);
        $image_style = $image_style_storage->load($this->configuration['image_style']);
      }
      if ($image_style) {
        $cache_tags = $image_style->getCacheTags();
        $build = [
          '#theme' => 'image_style',
          '#width' => $image->getWidth(),
          '#height' => $image->getHeight(),
          '#style_name' => $configuration['image_style'],
          '#uri' => $file->getFileUri(),
          '#cache' => [
            'tags' => $cache_tags,
          ],
        ];
      }
      else {
        $build = [
          '#theme' => 'image',
          '#width' => $image->getWidth(),
          '#height' => $image->getHeight(),
          '#uri' => $image->getSource(),
          '#cache' => [
            'tags' => $cache_tags,
          ],
        ];
      }
    }
    return $build;
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
