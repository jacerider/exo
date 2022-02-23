<?php

namespace Drupal\exo_modal\Controller;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\exo_modal\Ajax\ExoModalContentCommand;
use Drupal\exo_modal\Ajax\ExoModalInsertCommand;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a helper to determine if the current request is via AJAX.
 *
 * @internal
 */
trait ExoModalResponseTrait {

  /**
   * Build a modal.
   */
  protected function buildModalResponse(Request $request, $build, $settings = []) {
    $response = new AjaxResponse();
    $from_modal = !empty($request->query->get('from_modal'));
    // This request has been requested from an existing modal.
    if ($from_modal) {
      $response->addCommand(new ExoModalContentCommand($build));
      return $response;
    }
    $settings = NestedArray::mergeDeep($settings, $request->query->get('modal') ?: []);
    $modal = $this->exoModalGenerator->generate('exo_modal_' . time(), NestedArray::mergeDeep([
      'modal' => [
        'autoOpen' => TRUE,
        'destroyOnClose' => TRUE,
        'padding' => 20,
      ],
    ], $settings));
    $modal->setContent($build);
    $response->addCommand(new ExoModalInsertCommand('body', $modal->toRenderableModal()));
    return $response;
  }

}
