<?php

namespace Drupal\exo_alchemist\Plugin\Block;

use Drupal\layout_builder\Plugin\Block\InlineBlock;

/**
 * Defines an inline block plugin type.
 *
 * @Block(
 *  id = "global_block",
 *  admin_label = @Translation("Global block"),
 *  category = @Translation("Global blocks"),
 *  deriver = "Drupal\layout_builder\Plugin\Derivative\InlineBlockDeriver",
 * )
 *
 * @internal
 *   Plugin classes are internal.
 */
class GlobalBlock extends InlineBlock {

}
