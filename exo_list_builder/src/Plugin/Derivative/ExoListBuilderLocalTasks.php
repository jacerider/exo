<?php

namespace Drupal\exo_list_builder\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\exo_list_builder\ExoListBuilderContentStatesInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines dynamic local tasks.
 *
 * NOT CURRENTLY BEING USED.
 */
class ExoListBuilderLocalTasks extends DeriverBase implements ContainerDeriverInterface {

  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Creates a DynamicLocalTasks object.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The translation manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(TranslationInterface $string_translation, EntityTypeManagerInterface $entity_type_manager) {
    $this->stringTranslation = $string_translation;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('string_translation'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    /** @var \Drupal\exo_list_builder\EntityListInterface[] $exo_entity_lists */
    $exo_entity_lists = $this->entityTypeManager->getStorage('exo_entity_list')->loadMultiple();

    foreach ($exo_entity_lists as $exo_entity_list) {
      $handler = $exo_entity_list->getHandler();
      if ($handler instanceof ExoListBuilderContentStatesInterface) {
        $entity_type_id = $exo_entity_list->getTargetEntityTypeId();
        if ($exo_entity_list->isOverride()) {
          $this->derivatives['exo_list_builder.' . $exo_entity_list->id() . '.state.default'] = [
            'route_name' => "entity.$entity_type_id.collection",
            'title' => $handler->getDefaultStateLabel(),
            'options' => [
              'attributes' => [
                'data-icon' => $handler->getDefaultStateIcon(),
              ],
            ],
            'base_route' => "entity.$entity_type_id.collection",
            'weight' => -10,
          ] + $base_plugin_definition;
          foreach ($handler->getStates() as $state_id => $state) {
            $this->derivatives['exo_list_builder.' . $exo_entity_list->id() . '.state.' . $state_id] = [
              'route_name' => "entity.$entity_type_id.collection",
              'route_parameters' => [
                'state' => $state_id,
              ],
              'title' => $state['label'],
              'options' => [
                'attributes' => [
                  'data-icon' => $state['icon'],
                ],
              ],
              'base_route' => "entity.$entity_type_id.collection",
              'weight' => -10,
            ] + $base_plugin_definition;
          }
        }
      }
    }
    return $this->derivatives;
  }

}
