<?php

namespace Drupal\exo_list_builder\Plugin\ExoComponentField;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldComputedBase;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldDisplayFormTrait;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldPreviewEntityTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A 'view' adapter for exo components.
 *
 * @ExoComponentField(
 *   id = "exo_entity_list_filter",
 *   label = @Translation("Entity List Filter"),
 * )
 */
class ExoEntityListFilter extends ExoComponentFieldComputedBase implements ContainerFactoryPluginInterface {

  use ExoComponentFieldDisplayFormTrait;
  use ExoComponentFieldPreviewEntityTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * Constructs a LocalActionDefault object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The entity form builder.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, FormBuilderInterface $form_builder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->formBuilder = $form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('form_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processDefinition() {
    parent::processDefinition();
    $field = $this->getFieldDefinition();
    if (!$field->hasAdditionalValue('exo_entity_list_id')) {
      throw new PluginException(sprintf('eXo Component Field plugin (%s) requires [exo_entity_list_id] be set.', $field->getType()));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function propertyInfo() {
    $properties = [
      'render' => $this->t('The entity list filter renderable.'),
    ];
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function viewValue(ContentEntityInterface $entity, array $contexts) {
    $value = [];
    $field = $this->getFieldDefinition();
    /** @var \Drupal\exo_list_builder\EntityListInterface $entity */
    $entity = $this->entityTypeManager->getStorage('exo_entity_list')->load($field->getAdditionalValue('exo_entity_list_id'));
    if ($entity) {
      $redirectUrl = NULL;
      if ($route = $field->getAdditionalValue('exo_entity_list_redirect_route')) {
        $redirectUrl = Url::fromRoute($route, $field->getAdditionalValue('exo_entity_list_redirect_route_prameters') ?? []);
      }
      $value['render'] = $this->formBuilder->getForm('\Drupal\exo_list_builder\Form\EntityListFilterForm', $entity, $redirectUrl);
    }
    return $value;
  }

}
