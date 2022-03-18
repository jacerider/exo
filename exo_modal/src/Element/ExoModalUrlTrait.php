<?php

namespace Drupal\exo_modal\Element;

use Drupal\Core\Url;

/**
 * Provides a helper to determine if the current request is via AJAX.
 *
 * @internal
 */
trait ExoModalUrlTrait {

  /**
   * Build a modal URL.
   *
   * @return \Drupal\Core\Url
   *   The URL.
   */
  protected static function toModalUrl(Url $url) {
    $query = $url->getOption('query');
    $query['from_modal'] = 1;
    $url->setOption('query', $query);
    return $url;
  }

}
