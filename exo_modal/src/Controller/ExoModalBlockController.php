<?php

namespace Drupal\exo_modal\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\block\BlockInterface;
use Drupal\Core\Ajax\AjaxHelperTrait;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\exo_modal\Plugin\ExoModalBlockPluginInterface;
use Drupal\Core\Ajax\AppendCommand;
use Drupal\exo_modal\Ajax\ExoModalContentCommand;

/**
 * Class ExoModalBlockController.
 */
class ExoModalBlockController extends ControllerBase {
  use AjaxHelperTrait;

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new ExoModalBlockController object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * View dialog content.
   *
   * @param \Drupal\block\BlockInterface $block
   *   The eXo toolbar item.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The ajax response.
   */
  public function view(BlockInterface $block) {
    $build = [];
    if ($block->access('view')) {
      $plugin = $block->getPlugin();
      if ($plugin instanceof ExoModalBlockPluginInterface) {
        $build = $plugin->buildModal();
        if ($this->isAjax()) {
          $response = new AjaxResponse();
          $response->addCommand(new AppendCommand('body', $build));
          return $response;
        }
      }
      else {
        $build = [
          'messages' => [
            '#type' => 'status_messages',
          ],
          'block' => $this->entityTypeManager->getViewBuilder('block')->view($block),
        ];

        if ($this->isAjax()) {
          $response = new AjaxResponse();
          $response->addCommand(new ExoModalContentCommand($build));
          return $response;
        }
      }
    }
    return $build;
  }

}
