<?php

namespace Drupal\exo_modal\Form;

use Drupal\Core\Ajax\AjaxFormHelperTrait;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\MessageCommand;
use Drupal\Core\Ajax\RemoveCommand;
use Drupal\exo_modal\Ajax\ExoModalCloseCommand;

/**
 * Provides a helper to for submitting an AJAX form.
 *
 * @internal
 */
trait ExoModalFormAjaxTrait {
  use AjaxFormHelperTrait;

  /**
   * {@inheritdoc}
   */
  protected function ajaxMessages(AjaxResponse $response, $selector = '.region.status') {
    $messages = \Drupal::messenger()->deleteAll();
    $response->addCommand(new RemoveCommand($selector . ' > *'));
    if ($messages) {
      foreach ($messages as $type => $messages_by_type) {
        foreach ($messages_by_type as $message) {
          $response->addCommand(new MessageCommand($message, $selector, ['type' => $type], FALSE));
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function ajaxModalClose(AjaxResponse $response) {
    $response->addCommand(new ExoModalCloseCommand());
  }

}
