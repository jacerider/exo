<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Filter;

use Drupal\content_moderation\ModerationInformationInterface;
use Drupal\content_moderation\Plugin\WorkflowType\ContentModerationInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\exo_list_builder\EntityListInterface;
use Drupal\exo_list_builder\Plugin\ExoListFilterBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a eXo list element for rendering a content entity field.
 *
 * @ExoListFilter(
 *   id = "moderation_state",
 *   label = @Translation("Select"),
 *   description = @Translation("Filter by the moderation states."),
 *   weight = 0,
 *   field_type = {},
 *   entity_type = {},
 *   bundle = {},
 *   field_name = {
 *     "moderation_state",
 *   },
 *   exclusive = false,
 *   provider = "content_moderation",
 * )
 */
class ModerationState extends OptionsSelect implements ContainerFactoryPluginInterface {

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
  public function getValueOptions(EntityListInterface $entity_list, array $field, $input = NULL) {
    $options = [];
    $latest_options = [];
    foreach ($entity_list->getTargetBundleIds() as $bundle) {
      /** @var \Drupal\workflows\WorkflowInterface $workflow */
      $workflow = $this->moderationInformation->getWorkflowForEntityTypeAndBundle($entity_list->getTargetEntityTypeId(), $bundle);
      if ($workflow) {
        $plugin = $workflow->getTypePlugin();
        if ($plugin instanceof ContentModerationInterface) {
          foreach ($plugin->getStates() as $state_id => $state) {
            /** @var \Drupal\content_moderation\ContentModerationState $state */
            $options['Current Version'][$state_id] = $state->label();
            if (!$state->isDefaultRevisionState()) {
              $latest_options['Pending Version']['latest:' . $state_id] = $state->label();
            }
          }
        }
      }
    }
    return $options + $latest_options;
  }

  /**
   * {@inheritdoc}
   */
  public function queryAlter($query, $value, EntityListInterface $entity_list, array $field) {
    $query->addTag('exo_entity_list_moderation_state');
    $parts = explode(':', $value);
    if (isset($parts[1]) && $parts[0] === 'latest') {
      $value = $parts[1];
      $query->latestRevision();
    }
    // @see exo_list_builder_query_exo_entity_list_moderation_state_alter().
    $query->addMetaData('exo_entity_list_moderation_state', $value);
  }

}
