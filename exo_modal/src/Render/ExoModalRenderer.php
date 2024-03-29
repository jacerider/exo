<?php

namespace Drupal\exo_modal\Render;

use Drupal\Component\Utility\Html;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Controller\TitleResolverInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Render\MainContent\MainContentRendererInterface;
use Drupal\exo_modal\Ajax\ExoModalOpenCommand;

/**
 * Default main content renderer for dialog requests.
 */
class ExoModalRenderer implements MainContentRendererInterface {

  /**
   * The title resolver.
   *
   * @var \Drupal\Core\Controller\TitleResolverInterface
   */
  protected $titleResolver;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a new DialogRenderer.
   *
   * @param \Drupal\Core\Controller\TitleResolverInterface $title_resolver
   *   The title resolver.
   * @param Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(TitleResolverInterface $title_resolver, ModuleHandlerInterface $module_handler) {
    $this->titleResolver = $title_resolver;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public function renderResponse(array $main_content, Request $request, RouteMatchInterface $route_match) {
    $response = new AjaxResponse();

    // First render the main content, because it might provide a title.
    $content = \Drupal::service('renderer')->renderRoot($main_content);

    // Attach the library necessary for using the OpenDialogCommand and set the
    // attachments for this Ajax response.
    // $main_content['#attached']['library'][] = 'exo_modal/ajax';.
    $response->setAttachments($main_content['#attached']);

    // Determine the title: use the title provided by the main content if any,
    // otherwise get it from the routing information.
    $title = $main_content['#title'] ?? $this->titleResolver->getTitle($request, $route_match->getRouteObject());

    // Determine the dialog options and the target for the OpenDialogCommand.
    $options = $request->request->all()['dialogOptions'] ?? [];

    $route_name = $route_match->getRouteName();
    $id = Html::getUniqueId(md5("drupal-dialog-$route_name"));

    $options['padding'] = $options['padding'] ?? '1.25rem';
    $options['title'] = $options['title'] ?? $title;

    $response->addCommand(new ExoModalOpenCommand($id, $content, $options));
    return $response;
  }

}
