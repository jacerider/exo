<?php

namespace Drupal\exo_list_builder\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\exo_list_builder\EntityListInterface;
use Drupal\exo_list_builder\ExoListManagerInterface;
use Drupal\exo_list_builder\Plugin\ExoListFieldValuesInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Define the entity export csv download controller.
 */
class ExoListAutocomplete extends ControllerBase implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The list filter manager service.
   *
   * @var \Drupal\exo_list_builder\ExoListManagerInterface
   */
  protected $filterManager;

  /**
   * The entity export csv download constructor.
   */
  public function __construct(ExoListManagerInterface $filter_manager) {
    $this->filterManager = $filter_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.exo_list_filter')
    );
  }

  /**
   * Download exo list action export file.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The response.
   */
  public function results(Request $request, EntityListInterface $exo_entity_list, $field_id) {
    $field = $exo_entity_list->getField($field_id);
    $results = [];
    if (!empty($field['filter']['type'])) {
      $instance = $this->filterManager->createInstance($field['filter']['type'], $field['filter']['settings']);
      if ($instance instanceof ExoListFieldValuesInterface) {
        $input = $request->query->get('q');
        $count = 0;
        $limit = 10;
        foreach ($instance->getValueOptions($exo_entity_list, $field, $input) as $key => $value) {
          $results[] = [
            'value' => (string) $key,
            'label' => (string) $value,
          ];
          if ($count >= $limit) {
            break;
          }
          $count++;
        }
      }
    }
    return new JsonResponse($results);
  }

}
