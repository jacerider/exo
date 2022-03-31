<?php

namespace Drupal\exo_list_builder\Entity;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Url;
use Drupal\exo_list_builder\EntityListInterface;

/**
 * Defines the eXo Entity List entity.
 *
 * @ConfigEntityType(
 *   id = "exo_entity_list",
 *   label = @Translation("eXo Entity List"),
 *   label_plural = @Translation("eXo Entity Lists"),
 *   label_collection = @Translation("eXo Entity Lists"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\exo_list_builder\EntityListListBuilder",
 *     "view_builder" = "Drupal\exo_list_builder\EntityListViewBuilder",
 *     "access" = "Drupal\exo_list_builder\EntityListAccessControlHandler",
 *     "form" = {
 *       "add" = "Drupal\exo_list_builder\Form\EntityListAddForm",
 *       "edit" = "Drupal\exo_list_builder\Form\EntityListForm",
 *       "delete" = "Drupal\exo_list_builder\Form\EntityListDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\exo_list_builder\EntityListHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "exo_entity_list",
 *   admin_permission = "administer exo list builder",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "status" = "status"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "target_entity_type",
 *     "target_bundles_include",
 *     "target_bundles_exclude",
 *     "override",
 *     "format",
 *     "url",
 *     "limit",
 *     "limit_options",
 *     "actions",
 *     "sort",
 *     "fields",
 *     "settings",
 *     "weight",
 *   },
 *   lookup_keys = {
 *     "target_entity_type",
 *     "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/config/exo/list/{exo_entity_list}",
 *     "add-form" = "/admin/config/exo/list/add",
 *     "edit-form" = "/admin/config/exo/list/{exo_entity_list}/edit",
 *     "delete-form" = "/admin/config/exo/list/{exo_entity_list}/delete",
 *     "collection" = "/admin/config/exo/list"
 *   }
 * )
 */
class EntityList extends ConfigEntityBase implements EntityListInterface {

  /**
   * The eXo Entity List ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The eXo Entity List label.
   *
   * @var string
   */
  protected $label;

  /**
   * The target entity type id.
   *
   * @var string
   */
  protected $target_entity_type;

  /**
   * The target bundle ids to include.
   *
   * @var array
   */
  protected $target_bundles_include = [];

  /**
   * The target bundle ids to exclude.
   *
   * @var array
   */
  protected $target_bundles_exclude = [];

  /**
   * The override entity list builder flag.
   *
   * @var bool
   */
  protected $override = FALSE;

  /**
   * The entity list format.
   *
   * @var string
   */
  protected $format = 'table';

  /**
   * The entity list URL.
   *
   * @var string
   */
  protected $url = '';

  /**
   * The default limit.
   *
   * @var int
   */
  protected $limit = 10;

  /**
   * The limit options.
   *
   * @var array
   */
  protected $limit_options = [10, 20, 50, 100];

  /**
   * The action definitions.
   *
   * @var array
   */
  protected $actions = [];

  /**
   * The action plugin definitions.
   *
   * @var array
   */
  protected $actionDefinitions;

  /**
   * The sort default.
   *
   * @var string
   */
  protected $sort = '';

  /**
   * Various settings.
   *
   * @var array
   */
  protected $settings = [];

  /**
   * The field definitions.
   *
   * @var array
   */
  protected $fields = [];

  /**
   * The available field definitions.
   *
   * @var array
   */
  protected $fieldDefinitions;

  /**
   * The target bundle ids.
   *
   * @var array
   */
  protected $targetBundles;

  /**
   * The field defaults.
   *
   * @var array
   */
  protected static $fieldDefaults = [
    'label' => 'Unnamed',
    'type' => 'custom',
    'view' => [
      'type' => '',
      'settings' => [],
      'toggle' => FALSE,
      'show' => FALSE,
      'wrapper' => 'small',
      'sort' => NULL,
      'sort_asc_label' => '',
      'sort_desc_label' => '',
    ],
    'filter' => [
      'type' => '',
      'settings' => [],
    ],
    'alias_field' => NULL,
    'sort_field' => NULL,
    'weight' => 0,
  ];

  /**
   * The action defaults.
   *
   * @var array
   */
  protected $actionDefaults = [
    'settings' => [],
  ];

  /**
   * The setting defaults.
   *
   * @var array
   */
  protected $settingDefaults = [
    'operations_status' => TRUE,
  ];

  /**
   * The weight.
   *
   * @var int
   */
  protected $weight = 0;

  /**
   * The entity handler.
   *
   * @var \Drupal\exo_list_builder\EntityListInterface
   */
  protected $entityHandler;

  /**
   * The default field values.
   *
   * @return array
   *   The default field values.
   */
  public static function getFieldDefaults() {
    return static::$fieldDefaults;
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);
    \Drupal::service('router.builder')->rebuild();
  }

  /**
   * {@inheritdoc}
   */
  public function toUrl($rel = 'canonical', array $options = []) {
    if ($rel === 'canonical' && $this->getUrl()) {
      if (!empty($options['query']['exo'])) {
        $options['query']['exo'] = $this->optionsEncode($options['query']['exo']);
      }
      return Url::fromRoute('exo_list_builder.' . $this->id(), [], $options);
    }
    return parent::toUrl($rel, $options);
  }

  /**
   * {@inheritdoc}
   */
  public function toFilteredUrl(array $filters = [], array $options = []) {
    if (!empty($filters)) {
      $options['query']['exo']['filter'] = $filters;
    }
    return $this->toUrl('canonical', $options);
  }

  /**
   * {@inheritdoc}
   */
  public function optionsEncode($options) {
    return base64_encode(json_encode($options));
  }

  /**
   * {@inheritdoc}
   */
  public function optionsDecode($options) {
    return json_decode(base64_decode($options), TRUE);
  }

  /**
   * {@inheritDoc}
   */
  public function getTargetEntityTypeId() {
    return $this->target_entity_type;
  }

  /**
   * {@inheritDoc}
   */
  public function getTargetEntityType() {
    return $this->entityTypeManager()->getDefinition($this->getTargetEntityTypeId(), FALSE);
  }

  /**
   * {@inheritDoc}
   */
  public function getTargetBundleIds() {
    if (!isset($this->targetBundles)) {
      $include = $this->getTargetBundleIncludeIds();
      if (empty($include) && $target_entity_type = $this->getTargetEntityTypeId()) {
        $bundles = \Drupal::service('entity_type.bundle.info')->getBundleInfo($target_entity_type);
        foreach ($bundles as $bundle_id => $bundle) {
          $include[$bundle_id] = $bundle_id;
        }
      }
      $this->targetBundles = array_diff_key($include, $this->getTargetBundleExcludeIds());
    }
    return $this->targetBundles;
  }

  /**
   * {@inheritDoc}
   */
  public function getTargetBundleIncludeIds() {
    return $this->target_bundles_include;
  }

  /**
   * {@inheritDoc}
   */
  public function getTargetBundleExcludeIds() {
    return $this->target_bundles_exclude;
  }

  /**
   * {@inheritDoc}
   */
  public function isAllBundles() {
    return empty($this->getTargetBundleIncludeIds());
  }

  /**
   * {@inheritdoc}
   */
  public function allowOverride() {
    $entity_type_id = $this->getTargetEntityTypeId();
    $bundle_includes = $this->getTargetBundleIncludeIds();
    $bundle_excludes = $this->getTargetBundleExcludeIds();
    $bundles = $this->getTargetBundleIds();
    if (
      // An entity type without bundles.
      (count($bundles) === 1 && key($bundles) === $this->getTargetEntityTypeId()) ||
      // An entity type with all bundles.
      (empty($bundle_includes) && empty($bundle_excludes)) ||
      $entity_type_id === 'taxonomy_term' && count($bundles) === 1
    ) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function isOverride() {
    return $this->allowOverride() ? !empty($this->override) : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormat() {
    return $this->format;
  }

  /**
   * {@inheritdoc}
   */
  public function getUrl() {
    return $this->url;
  }

  /**
   * {@inheritdoc}
   */
  public function getLimit() {
    return $this->limit ?? 0;
  }

  /**
   * {@inheritdoc}
   */
  public function getLimitOptions() {
    return array_combine($this->limit_options, $this->limit_options);
  }

  /**
   * {@inheritdoc}
   */
  public function showOperations() {
    return !empty($this->getSetting('operations_status'));
  }

  /**
   * {@inheritdoc}
   */
  public function getSort() {
    return $this->sort;
  }

  /**
   * {@inheritdoc}
   */
  public function getActions() {
    $actions = [];
    $definitions = $this->getAvailableActions();
    foreach ($this->actions as $action_id => $action) {
      if (isset($definitions[$action_id])) {
        $actions[$action_id] = NestedArray::mergeDeep($action + $definitions[$action_id]);
      }
    }
    return $actions;
  }

  /**
   * Get action definitions.
   *
   * @return array
   *   An array of action definitions.
   */
  public function getAvailableActions() {
    if (!isset($this->actionDefinitions)) {
      /** @var \Drupal\exo_list_builder\ExoListActionManagerInterface $manager */
      $manager = \Drupal::service('plugin.manager.exo_list_action');
      $actions = $manager->getFieldOptions($this->getTargetEntityTypeId(), $this->getTargetBundleIds());
      foreach ($actions as $action_id => $label) {
        $this->actionDefinitions[$action_id] = [
          'id' => $action_id,
          'label' => $label,
        ] + $this->actionDefaults;
      }
    }
    return $this->actionDefinitions;
  }

  /**
   * {@inheritDoc}
   */
  public function getFields() {
    $fields = [];
    $available_fields = $this->getAvailableFields();
    foreach ($this->fields as $field_id => $field) {
      if (!isset($available_fields[$field_id])) {
        continue;
      }
      // Merge all data.
      $field = NestedArray::mergeDeep($available_fields[$field_id], $field);
      // Always show if field cannot be toggled.
      if (empty($field['view']['toggle'])) {
        $field['view']['show'] = TRUE;
      }
      $fields[$field_id] = $field;
    }

    return $fields;
  }

  /**
   * {@inheritDoc}
   */
  public function setFields(array $fields) {
    $this->fields = $fields;
    return $this;
  }

  /**
   * {@inheritDoc}
   */
  public function getAvailableFields() {
    if (!isset($this->fieldDefinitions)) {
      $fields = $this->getHandler()->loadFields();
      foreach ($fields as $field_id => &$field) {
        $defaults = static::getFieldDefaults();
        $defaults['view']['sort_asc_label'] = '@label: Up';
        $defaults['view']['sort_desc_label'] = '@label: Down';
        $field = NestedArray::mergeDeep($defaults, $field);
        $field['id'] = $field_id;
        $field['field_name'] = $field_id;
        if (empty($field['display_label'])) {
          $field['display_label'] = $field['label'];
        }
      }
      foreach ($fields as $field_id => &$field) {
        if (!empty($field['alias_field']) && isset($fields[$field['alias_field']])) {
          // Alias the field as if it were another field.
          $alias = $fields[$field['alias_field']];
          $field['type'] = $alias['type'];
          $field['field_name'] = $alias['field_name'];
          $field['sort_field'] = $alias['sort_field'];
          if (isset($alias['definition'])) {
            $field['definition'] = $alias['definition'];
          }
          $field['sort_field'] = $alias['sort_field'];
        }
      }
      $this->fieldDefinitions = $fields;
    }
    return $this->fieldDefinitions;
  }

  /**
   * {@inheritDoc}
   */
  public function hasField($field_name) {
    return !empty($this->getField($field_name));
  }

  /**
   * {@inheritDoc}
   */
  public function getField($field_name) {
    $fields = $this->getFields();
    return isset($fields[$field_name]) ? $fields[$field_name] : NULL;
  }

  /**
   * {@inheritDoc}
   */
  public function getFieldValue($field_name, $key) {
    $default = static::getFieldDefaults();
    $field = $this->getField($field_name);
    return isset($field[$key]) ? $field[$key] : (isset($default[$key]) ? $default[$key] : NULL);
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight() {
    return $this->weight;
  }

  /**
   * {@inheritdoc}
   */
  public function setWeight($weight) {
    $this->weight = $weight;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getHandlerClass() {
    return $this->getTargetEntityType()->getHandlerClass('exo_list_builder');
  }

  /**
   * {@inheritdoc}
   */
  public function getHandler() {
    if (!isset($this->entityHandler)) {
      $this->entityHandler = $this->entityTypeManager()->getHandler($this->getTargetEntityTypeId(), 'exo_list_builder');
      $this->entityHandler->setEntityList($this);
    }
    return $this->entityHandler;
  }

  /**
   * {@inheritdoc}
   */
  public function getSettings() {
    return NestedArray::mergeDeep($this->settingDefaults, $this->settings);
  }

  /**
   * {@inheritdoc}
   */
  public function getSetting($key = '', $default = NULL) {
    $settings = $this->getSettings();
    $key_exists = NULL;
    $value = &NestedArray::getValue($settings, (array) $key, $key_exists);
    return $key_exists ? $value : $default;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    parent::calculateDependencies();

    $target_entity_type = $this->getTargetEntityType();
    if ($target_entity_type && !$this->isAllBundles()) {
      foreach ($this->getTargetBundleIds() as $bundle_id) {
        $dependency = $target_entity_type->getBundleConfigDependency($bundle_id);
        $this->addDependency($dependency['type'], $dependency['name']);
      }
    }

    return $this->getDependencies();
  }

}
