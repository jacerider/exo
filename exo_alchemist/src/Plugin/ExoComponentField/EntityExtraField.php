<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\Context\EntityContext;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldComputedBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A 'extra field' adapter for exo components.
 *
 * @ExoComponentField(
 *   id = "extra_field",
 *   deriver = "\Drupal\exo_alchemist\Plugin\Derivative\ExoComponentExtraFieldEntityDeriver"
 * )
 */
class EntityExtraField extends ExoComponentFieldComputedBase implements ContextAwarePluginInterface, ContainerFactoryPluginInterface {

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * The field name.
   *
   * @var string
   */
  protected $fieldName;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new FieldBlock.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, EntityFieldManagerInterface $entity_field_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityFieldManager = $entity_field_manager;
    // Get field name from the plugin ID.
    $parts = explode(static::DERIVATIVE_SEPARATOR, $plugin_id);
    if (count($parts) === 3) {
      $field_name = $parts[2];
    }
    if (count($parts) === 4) {
      $field_name = $parts[3];
    }
    assert(!empty($field_name));
    $this->fieldName = $field_name;

    parent::__construct($configuration, $plugin_id, $plugin_definition);
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
      $container->get('entity_field.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function propertyInfo() {
    $properties = [
      'render' => $this->t('The rendered field.'),
    ];
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function view(ContentEntityInterface $entity, array $contexts) {
    if ($this->isPreview($contexts)) {
      $parts = explode(static::DERIVATIVE_SEPARATOR, $this->pluginId);
      $entity_type_id = $parts[1];
      if (count($parts) === 3) {
        $entity_type_bundle_info = \Drupal::service('entity_type.bundle.info');
        $bundles = $entity_type_bundle_info->getBundleInfo($entity_type_id);
        $bundle = key($bundles);
      }
      if (count($parts) === 4) {
        $bundle = $parts[2];
      }
      $sample_entity = \Drupal::service('layout_builder.sample_entity_generator')->get($entity_type_id, $bundle);
      $this->setContext('entity', EntityContext::fromEntity($sample_entity));
    }
    return parent::view($entity, $contexts);
  }

  /**
   * {@inheritdoc}
   */
  public function viewValue(ContentEntityInterface $entity, array $contexts) {
    $parent_entity = $this->getParentEntity();
    // Add a placeholder to replace after the entity view is built.
    // @see layout_builder_entity_view_alter().
    $extra_fields = $this->entityFieldManager->getExtraFields($parent_entity->getEntityTypeId(), $parent_entity->bundle());
    if (!isset($extra_fields['display'][$this->fieldName])) {
      $build = [];
    }
    else {
      // Render just the extra field. The only way to do this is to fully render
      // the field.
      /** @var \Drupal\Core\Entity\EntityViewBuilder $entity_view_builder */
      $view_mode = 'extra_field';
      $displays = EntityViewDisplay::collectRenderDisplays([$parent_entity], $view_mode);
      $display = reset($displays);
      foreach ($display->getComponents() as $name => $component) {
        $display->removeComponent($name);
      }
      $display->setComponent($this->fieldName);
      $view_hook = "{$parent_entity->getEntityTypeId()}_view";
      $module_handler = \Drupal::moduleHandler();
      $entity_build = [];
      $module_handler->invokeAll($view_hook, [&$entity_build, $parent_entity, $display, $view_mode]);
      $module_handler->invokeAll('entity_view', [&$entity_build, $entity, $display, $view_mode]);
      $build = [];
      if (isset($entity_build[$this->fieldName])) {
        $build = $entity_build[$this->fieldName];
      }
    }
    CacheableMetadata::createFromObject($this)->applyTo($build);
    return [
      'render' => $build,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getPreviewFallbackString() {
    $parent_entity = $this->getParentEntity();
    $extra_fields = $this->entityFieldManager->getExtraFields($parent_entity->getEntityTypeId(), $parent_entity->bundle());
    return new TranslatableMarkup('"@field" field', ['@field' => $extra_fields['display'][$this->fieldName]['label']]);
  }

  /**
   * {@inheritdoc}
   */
  protected function componentAccess(array $contexts, AccountInterface $account) {
    return $this->getparentEntity()->access('view', $account, TRUE);
  }

}
