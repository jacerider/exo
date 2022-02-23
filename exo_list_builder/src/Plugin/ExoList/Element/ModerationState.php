<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Element;

use Drupal\content_moderation\ModerationInformationInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\exo_list_builder\Plugin\ExoListContentTrait;
use Drupal\exo_list_builder\Plugin\ExoListElementBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a eXo list element for rendering the moderation state.
 *
 * @ExoListElement(
 *   id = "moderation_state",
 *   label = @Translation("Moderation State"),
 *   description = @Translation("The moderation state label."),
 *   weight = 0,
 *   field_type = {},
 *   entity_type = {},
 *   bundle = {},
 *   field_name = {
 *     "moderation_state",
 *   },
 *   exclusive = true,
 *   provider = "content_moderation",
 * )
 */
class ModerationState extends ExoListElementBase implements ContainerFactoryPluginInterface {
  use ExoListContentTrait;

  /**
   * The moderation information service.
   *
   * @var \Drupal\content_moderation\ModerationInformationInterface
   */
  protected $moderationInformation;

  /**
   * LogGeneratorBase constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\content_moderation\ModerationInformationInterface $moderation_information
   *   The moderation information service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ModerationInformationInterface $moderation_information) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->moderationInformation = $moderation_information;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('content_moderation.moderation_information')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function view(EntityInterface $entity, array $field) {
    if ($item = $this->getItem($entity, $field)) {
      $workflow = $this->moderationInformation->getWorkflowForEntity($entity);
      return $workflow->getTypePlugin()->getState($item->value)->label();
    }
    return parent::view($entity, $field);
  }

}
