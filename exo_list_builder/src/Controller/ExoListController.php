<?php

namespace Drupal\exo_list_builder\Controller;

use Drupal\Core\Ajax\AjaxHelperTrait;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\exo_icon\ExoIconTranslationTrait;
use Drupal\exo_list_builder\EntityListInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines a generic controller to list entities.
 */
class ExoListController extends ControllerBase {
  use ExoIconTranslationTrait;
  use AjaxHelperTrait;

  /**
   * Provides the listing page for any entity type.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   * @param \Drupal\exo_list_builder\EntityListInterface $exo_entity_list
   *   The exo entity list to render.
   *
   * @return array
   *   A render array as expected by
   *   \Drupal\Core\Render\RendererInterface::render().
   */
  public function listing(Request $request, EntityListInterface $exo_entity_list) {
    $handler = $exo_entity_list->getHandler();
    $lkey = $request->query->get('lkey');
    $query_conditions = $request->query->get('lqc');
    if ($query_conditions) {
      foreach ($exo_entity_list->optionsDecode($query_conditions) as $condition) {
        $handler->addQueryCondition($condition['field'], $condition['value'], $condition['operator'], $condition['langcode']);
      }
    }
    if ($lkey) {
      $pagerer_header = $exo_entity_list->getSetting('pagerer_header');
      $pagerer_footer = $exo_entity_list->getSetting('pagerer_footer');
      if ($pagerer_header === '_show_all' || $pagerer_footer === '_show_all') {
        $handler->setLimit(0);
      }
    }
    if ($this->isAjax()) {
      $response = new AjaxResponse();
      $response->addCommand(new ReplaceCommand('[data-list-key="' . $lkey . '"]', $handler->render()));
      return $response;
    }
    return $handler->render();
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
