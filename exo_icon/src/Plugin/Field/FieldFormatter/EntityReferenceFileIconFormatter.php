<?php

namespace Drupal\exo_icon\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\exo_media\Plugin\Field\FieldFormatter\ExoMediaFormatterTrait;

/**
 * Plugin implementation of the 'exo image' formatter.
 *
 * @FieldFormatter(
 *   id = "entity_reference_file_icon",
 *   label = @Translation("eXo File Icon"),
 *   provider = "exo_media",
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class EntityReferenceFileIconFormatter extends FileIconFormatter {
  use ExoMediaFormatterTrait;

  /**
   * An array of values keyed by media bundle.
   *
   * @var array
   */
  protected $mediaOtherValues = [];

  /**
   * {@inheritdoc}
   *
   * This has to be overriden because FileFormatterBase expects $item to be
   * of type \Drupal\file\Plugin\Field\FieldType\FileItem and calls
   * isDisplayed() which is not in FieldItemInterface.
   */
  protected function needsEntityLoad(EntityReferenceItem $item) {
    return !$item->hasNewEntity();
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntitiesToView(EntityReferenceFieldItemListInterface $items, $langcode) {
    return $this->mediaGetEntitiesToView($items, $langcode, FALSE);
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    foreach ($this->mediaOtherValues as $delta => $url) {
      $item = $items->get($delta);
      $options = [];
      $link_text = !empty($this->getSetting('title')) ? $this->getSetting('title') : $item->entity->label();
      $position = $this->getSetting('position');
      $link_text = $this->icon($link_text)->setIcon($this->mimeManager()->getMimeIcon('video/mp4'), $this->getSetting('package'));
      if ($position == 'after') {
        $link_text->setIconAfter();
      }
      if ($this->getSetting('text_only')) {
        $elements[$delta]['#markup'] = $link_text;
      }
      else {
        $elements[$delta] = Link::fromTextAndUrl($link_text, Url::fromUri($url, $options, [
          'target' => '_blank',
        ]))->toRenderable();
      }
      $elements[$delta]['#cache']['tags'] = $item->entity->getCacheTags();
    }
    ksort($elements);
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    $target_type = $field_definition->getFieldStorageDefinition()->getSetting('target_type');
    if ($target_type !== 'media') {
      return FALSE;
    }

    return parent::isApplicable($field_definition);
  }

}
