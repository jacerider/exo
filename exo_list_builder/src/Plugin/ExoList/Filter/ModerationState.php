<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Filter;

use Drupal\content_moderation\ModerationInformationInterface;
use Drupal\content_moderation\Plugin\WorkflowType\ContentModerationInterface;
use Drupal\Core\Entity\Query\QueryInterface;
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
 *   label = @Translation("Moderation State"),
 *   description = @Translation("Filter by the moderation states."),
 *   weight = 0,
 *   field_type = {},
 *   entity_type = {},
 *   bundle = {},
 *   field_name = {
 *     "moderation_state",
 *   },
 *   exclusive = FALSE,
 *   provider = "content_moderation",
 * )
 */
class ModerationState extends ExoListFilterBase implements ContainerFactoryPluginInterface {

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
  public function buildForm(array $form, FormStateInterface $form_state, $value, EntityListInterface $entity_list, array $field) {
    $form = parent::buildForm($form, $form_state, $value, $entity_list, $field);
    $form['state'] = [
      '#type' => 'select',
      '#title' => $field['display_label'],
      '#options' => $this->getWorkflowOptions($entity_list),
      '#empty_option' => $this->t('- Show All -'),
      '#default_value' => $value,
    ];
    return $form;
  }

  /**
   * Get workflow options.
   */
  protected function getWorkflowOptions(EntityListInterface $entity_list) {
    $options = [];
    foreach ($entity_list->getTargetBundleIds() as $bundle) {
      /** @var \Drupal\workflows\WorkflowInterface $workflow */
      $workflow = $this->moderationInformation->getWorkflowForEntityTypeAndBundle($entity_list->getTargetEntityTypeId(), $bundle);
      if ($workflow) {
        $plugin = $workflow->getTypePlugin();
        if ($plugin instanceof ContentModerationInterface) {
          foreach ($plugin->getStates() as $state_id => $state) {
            $options[$state_id] = $state->label();
          }
        }
      }
    }
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function toUrlQuery(array $raw_value, EntityListInterface $entity_list, array $field) {
    return $raw_value['state'];
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty($raw_value) {
    return empty($raw_value['state']);
  }

  /**
   * {@inheritdoc}
   */
  public function toPreview($value, EntityListInterface $entity_list, array $field) {
    $options = $this->getWorkflowOptions($entity_list);
    return $options[$value] ?? '';
  }

  /**
   * {@inheritdoc}
   */
  public function queryAlter(QueryInterface $query, $value, EntityListInterface $entity_list, array $field) {
    $query->addTag('exo_entity_list_moderation_state');
    // @see exo_list_builder_query_exo_entity_list_moderation_state_alter().
    $query->addMetaData('exo_entity_list_moderation_state', $value);
  }

}
