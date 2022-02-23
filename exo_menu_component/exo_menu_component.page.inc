<?php

/**
 * @file
 * Contains exo_menu_component.page.inc.
 *
 * Page callback for config pages.
 */

use Drupal\Core\Link;
use Drupal\Core\Render\Element;
use Drupal\Core\Url;

/**
 * Prepares variables for config page templates.
 *
 * Default template: exo-menu-component.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_exo_menu_component(array &$variables) {
  // Fetch SiteSettings Entity Object.
  $exo_menu_component = $variables['elements']['#exo_menu_component'];
  $variables['tag'] = 'div';

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}

/**
 * Prepares variables for list of available node type templates.
 *
 * Default template: exo-menu-component-add-list.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - content: An array of content types.
 */
function template_preprocess_exo_menu_component_add_list(array &$variables) {
  $variables['types'] = [];
  if (!empty($variables['content'])) {
    foreach ($variables['content'] as $type) {
      $variables['types'][$type->id()] = [
        'type' => $type->id(),
        'add_link' => Link::fromTextAndUrl($type->label(), Url::fromRoute('exo_menu_component.add_component', [
          'menu' => $variables['menu'],
          'exo_menu_component_type' => $type->id(),
        ], [
          'query' => \Drupal::service('redirect.destination')->getAsArray(),
        ]))->toString(),
      ];
    }
  }
}
