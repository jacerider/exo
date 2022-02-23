<?php

namespace Drupal\exo\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceLabelFormatter;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'entity reference list' formatter.
 *
 * @FieldFormatter(
 *   id = "entity_reference_list",
 *   label = @Translation("Label as List"),
 *   description = @Translation("Display the label of the referenced entities seperated with a list."),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class ExoEntityReferenceListFormatter extends EntityReferenceLabelFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    return [
      [
        '#theme' => 'item_list',
        '#items' => $elements,
      ],
    ];
  }

}
