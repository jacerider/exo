<?php

namespace Drupal\exo_reverse_entity_reference\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceLabelFormatter;

/**
 * Plugin implementation of the 'reverse entity reference label' formatter.
 *
 * @FieldFormatter(
 *   id = "exo_reverse_entity_reference_label",
 *   label = @Translation("Label"),
 *   description = @Translation("Display the label of the referenced entities."),
 *   field_types = {
 *     "exo_reverse_entity_reference"
 *   }
 * )
 */
class ExoReverseEntityReferenceLabelFormatter extends EntityReferenceLabelFormatter {
}
