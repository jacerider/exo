<?php

namespace Drupal\exo_list_builder\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\exo_icon\ExoIconTranslationTrait;
use Drupal\exo_list_builder\EntityListInterface;

/**
 * Defines a generic controller to list entities.
 */
class ExoListController extends ControllerBase {
  use ExoIconTranslationTrait;

  /**
   * Provides the listing page for any entity type.
   *
   * @param \Drupal\exo_list_builder\EntityListInterface $exo_entity_list
   *   The exo entity list to render.
   *
   * @return array
   *   A render array as expected by
   *   \Drupal\Core\Render\RendererInterface::render().
   */
  public function listing(EntityListInterface $exo_entity_list) {
    return $exo_entity_list->getHandler()->render();
  }

  /**
   * Provides the listing page for any entity type.
   *
   * @param \Drupal\exo_list_builder\EntityListInterface $exo_entity_list
   *   The exo entity list to render.
   *
   * @return array
   *   A render array as expected by
   *   \Drupal\Core\Render\RendererInterface::render().
   */
  public function listingTitle(EntityListInterface $exo_entity_list) {
    $label = $exo_entity_list->label();
    if (strpos($label, 'Manage ') !== FALSE) {
      $icon = $this->icon(str_replace('Manage ', '', $label))->match();
      if ($icon = $icon->getIcon()) {
        return $this->icon('@label', [
          '@label' => $label,
        ])->setIcon($icon->getId());
      }
    }
    return $label;
  }

}
