<?php

namespace Drupal\exo_oembed\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\exo_icon\ExoIconTranslationTrait;

/**
 * Plugin implementation of the thumbnail field formatter.
 *
 * @FieldFormatter(
 *   id = "exo_oembed_media_modal",
 *   label = @Translation("eXo OEmbed Modal"),
 *   provider = "exo_modal",
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class ExoOEmbedMediaModal extends ExoOEmbedModal {
  use ExoIconTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $media_items = [];
    foreach ($items as $delta => $item) {
      $media = $item->entity;
      if ($media) {
        $source_field = $media->getSource()->getConfiguration()['source_field'];
        $media_items = array_merge($media_items, parent::viewElements($media->get($source_field), $langcode));
      }
    }
    return $media_items;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    $target_type = $field_definition->getFieldStorageDefinition()->getSetting('target_type');
    return $target_type === 'media';
  }

}
