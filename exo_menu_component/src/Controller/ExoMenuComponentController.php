<?php

namespace Drupal\exo_menu_component\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\exo_menu_component\Entity\MenuComponentTypeInterface;
use Drupal\system\MenuInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ExoMenuComponentController.
 */
class ExoMenuComponentController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs a NodeController object.
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   */
  public function __construct(RendererInterface $renderer) {
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('renderer')
    );
  }

  /**
   * Addpage.
   *
   * @return string
   *   Return Hello string.
   */
  public function addComponentList($menu) {
    $build = [
      '#theme' => 'exo_menu_component_add_list',
      '#menu' => $menu,
      '#cache' => [
        'tags' => $this->entityTypeManager()->getDefinition('exo_menu_component_type')->getListCacheTags(),
      ],
    ];

    $content = [];

    // Only use menu_component types the user has access to.
    foreach ($this->entityTypeManager()->getStorage('exo_menu_component_type')->loadMultiple() as $type) {
      $access = $this->entityTypeManager()->getAccessControlHandler('exo_menu_component')->createAccess($type->id(), NULL, [], TRUE);
      if ($access->isAllowed()) {
        $content[$type->id()] = $type;
      }
      $this->renderer->addCacheableDependency($build, $access);
    }

    // Bypass the menu_component/add listing if only one content type is
    // available.
    if (count($content) == 1) {
      $type = array_shift($content);
      return $this->redirect('exo_menu_component.add_component', [
        'menu' => $menu,
        'exo_menu_component_type' => $type->id(),
      ]);
    }

    $build['#content'] = $content;
    return $build;
  }

  /**
   * Provides the menu link creation form.
   *
   * @param \Drupal\system\MenuInterface $menu
   *   An entity representing a custom menu.
   *
   * @return array
   *   Returns the menu link creation form.
   */
  public function addComponent(MenuInterface $menu, MenuComponentTypeInterface $exo_menu_component_type) {
    $menu_link = $this->entityTypeManager()
      ->getStorage('exo_menu_component')
      ->create([
        'menu_name' => $menu->id(),
        'type' => $exo_menu_component_type->id(),
      ]);
    return $this->entityFormBuilder()->getForm($menu_link);
  }

}
