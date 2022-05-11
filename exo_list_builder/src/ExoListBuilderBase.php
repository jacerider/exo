<?php

namespace Drupal\exo_list_builder;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Routing\RedirectDestinationTrait;
use Drupal\Core\Url;
use Drupal\exo_icon\ExoIconTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a base class for exo list builder.
 */
abstract class ExoListBuilderBase extends EntityListBuilder implements ExoListBuilderInterface {

  use ExoIconTranslationTrait;
  use RedirectDestinationTrait;

  /**
   * The key to use for the form element containing the entities.
   *
   * @var string
   */
  protected $entitiesKey = 'entities';

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The list field manager service.
   *
   * @var \Drupal\exo_list_builder\ExoListFieldManagerInterface
   */
  protected $fieldManager;

  /**
   * The list element manager service.
   *
   * @var \Drupal\exo_list_builder\ExoListManagerInterface
   */
  protected $elementManager;

  /**
   * The list filter manager service.
   *
   * @var \Drupal\exo_list_builder\ExoListManagerInterface
   */
  protected $filterManager;

  /**
   * The entity list.
   *
   * @var \Drupal\exo_list_builder\EntityListInterface
   */
  protected $entityList;

  /**
   * The list options.
   *
   * @var array
   */
  protected $options;

  /**
   * The total number of results.
   *
   * @var int
   */
  protected $total;

  /**
   * The number of entities to list per page, or FALSE to list all entities.
   *
   * For example, set this to FALSE if the list uses client-side filters that
   * require all entities to be listed (like the views overview).
   *
   * @var int|false
   */
  protected $limit = 20;

  /**
   * The shown fields.
   *
   * @var array
   */
  protected $fields;

  /**
   * The fields with filters enabled.
   *
   * @var array
   */
  protected $filters;

  /**
   * The fields with expsoed filters.
   *
   * @var array
   */
  protected $exposedFilters;

  /**
   * An array of query conditions.
   *
   * @var array
   */
  protected $queryConditions = [];

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('form_builder'),
      $container->get('module_handler'),
      $container->get('plugin.manager.exo_list_field'),
      $container->get('plugin.manager.exo_list_element'),
      $container->get('plugin.manager.exo_list_filter')
    );
  }

  /**
   * Constructs a new EntityListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\exo_list_builder\ExoListFieldManagerInterface $field_manager
   *   The field manager service.
   * @param \Drupal\exo_list_builder\ExoListManagerInterface $element_manager
   *   The element manager service.
   * @param \Drupal\exo_list_builder\ExoListManagerInterface $filter_manager
   *   The filter manager service.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, FormBuilderInterface $form_builder, ModuleHandlerInterface $module_handler, ExoListFieldManagerInterface $field_manager, ExoListManagerInterface $element_manager, ExoListManagerInterface $filter_manager) {
    $this->entityTypeId = $entity_type->id();
    $this->storage = $storage;
    $this->entityType = $entity_type;
    $this->formBuilder = $form_builder;
    $this->moduleHandler = $module_handler;
    $this->fieldManager = $field_manager;
    $this->elementManager = $element_manager;
    $this->filterManager = $filter_manager;
  }

  /**
   * {@inheritDoc}
   */
  public function loadFields() {
    if (!isset($this->fields)) {
      $entity_list = $this->getEntityList();
      $fields = [];
      foreach ($entity_list->getTargetBundleIds() as $bundle) {
        $fields += $this->fieldManager->getFields($entity_list->getTargetEntityTypeId(), $bundle);
      }
      $fields += $this->discoverFields();
      $this->alterFields($fields);
      $this->moduleHandler->alter('exo_list_builder_fields', $fields, $this->entityTypeId);
      $this->fields = $fields;
    }
    return $this->fields;
  }

  /**
   * Allow builder to modify field list.
   */
  protected function alterFields(&$fields) {
  }

  /**
   * {@inheritDoc}
   */
  protected function discoverFields() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return $this->entityTypeId . '_list';
  }

  /**
   * {@inheritDoc}
   */
  public function getEntityList() {
    return $this->entityList;
  }

  /**
   * {@inheritDoc}
   */
  public function setEntityList(EntityListInterface $entity_list) {
    $this->entityList = $entity_list;
    $this->buildOptions();
  }

  /**
   * Get options defaults.
   *
   * @return array
   *   The defaults.
   */
  protected function getOptionDefaults() {
    $entity_list = $this->getEntityList();
    return [
      'order' => NULL,
      'sort' => NULL,
      'page' => 0,
      // Flag indicating if list has been changed by the user.
      'm' => NULL,
      'limit' => $entity_list->getLimit(),
      'show' => [],
      'filter' => [],
    ];
  }

  /**
   * Build the query options.
   */
  protected function buildOptions() {
    $query = \Drupal::request()->query->all();
    if (!empty($query['exo'])) {
      $query += $this->getEntityList()->optionsDecode($query['exo']);
    }
    $this->setOptions($query);
  }

  /**
   * Get the query options.
   *
   * @return array
   *   The query options.
   */
  protected function getOptions() {
    return $this->options;
  }

  /**
   * Get a query option.
   *
   * @return mixed
   *   The query options.
   */
  public function getOption($key, $default_value = NULL) {
    $exists = NULL;
    $options = $this->getOptions();
    if (!empty($options)) {
      $option = NestedArray::getValue($options, (array) $key, $exists);
    }
    return $exists ? $option : $default_value;
  }

  /**
   * Set a query option.
   *
   * @return $this
   */
  protected function setOptions(array $options) {
    $defaults = $this->getOptionDefaults();
    $this->options = array_intersect_key($options + $defaults, $defaults);
    return $this;
  }

  /**
   * Set a query option.
   *
   * @return $this
   */
  protected function setOption($key, $value) {
    NestedArray::setValue($this->options, (array) $key, $value);
    return $this;
  }

  /**
   * Get options url.
   *
   * @param array $exclude_options
   *   An array of query options to exclude.
   * @param array $exclude_filters
   *   An array of query filters to exclude.
   * @param array $query
   *   Additional query parameters.
   *
   * @return \Drupal\Core\Url
   *   The url.
   */
  protected function getOptionsUrl(array $exclude_options = [], array $exclude_filters = [], array $query = []) {
    $entity_list = $this->getEntityList();
    $options = $this->getOptions();
    $defaults = $this->getOptionDefaults();
    $options_query = \Drupal::request()->query->all();
    $options_query = array_diff_key($options_query, $defaults);
    $query = NestedArray::mergeDeep($options_query, $query);
    unset($query['exo']);
    $query['m'] = 1;
    unset($options['order']);
    unset($options['sort']);
    unset($options['page']);
    if (!empty($options['limit']) && (int) $options['limit'] !== $entity_list->getLimit()) {
      $query['limit'] = $options['limit'];
    }
    unset($options['limit']);
    foreach ($options as $key => $value) {
      if (!empty($value) && isset($defaults[$key]) && !in_array($key, $exclude_options)) {
        if ($key === 'filter') {
          $value = array_diff_key($value, array_flip($exclude_filters));
          if (empty($value)) {
            continue;
          }
        }
        $query['exo'][$key] = $value;
      }
    }
    $url = Url::fromRoute('<current>');
    if (!empty($query['exo'])) {
      $query['exo'] = $this->getEntityList()->optionsEncode($query['exo']);
    }
    $url->setOption('query', $query);
    return $url;
  }

  /**
   * Loads entity IDs using a pager sorted by the entity id.
   *
   * @return array
   *   An array of entity IDs.
   */
  protected function getEntityIds() {
    $query = $this->getQuery();

    // Only add the pager if a limit is specified.
    if ($limit = $this->getOption('limit')) {
      $query->pager($limit);
    }

    return $query->execute();
  }

  /**
   * {@inheritDoc}
   */
  public function getQuery() {
    $entity_list = $this->getEntityList();
    $query = $this->getStorage()->getQuery()->accessCheck(TRUE);

    if ($entity_list->getFormat() === 'table') {
      $header = $this->buildHeader();
      foreach ($header as $field => $info) {
        if (is_array($info) && !empty($info['sort'])) {
          $query->tableSort($header);
          break;
        }
      }
    }
    else {
      $this->addQuerySort($query);
    }

    if ($entity_list->getTargetEntityType()->hasKey('bundle')) {
      $query->condition($entity_list->getTargetEntityType()->getKey('bundle'), $entity_list->getTargetBundleIds(), 'IN');
    }

    // Use an set query conditions.
    foreach ($this->queryConditions as $condition) {
      if ($condition['field'] === 'moderation_state') {
        $query->addTag('exo_entity_list_moderation_state');
        // @see exo_list_builder_query_exo_entity_list_moderation_state_alter().
        $query->addMetaData('exo_entity_list_moderation_state', $condition['value']);
      }
      else {
        $query->condition($condition['field'], $condition['value'], $condition['operator'], $condition['langcode']);
      }
    }

    // Filter.
    foreach ($this->getFilters() as $field_id => $field) {
      if (!$field['filter']['instance']) {
        continue;
      }
      // Non-exposed fields that have a default value set.
      if (empty($field['filter']['settings']['expose']) && !empty($field['filter']['settings']['default'])) {
        $filter_value = $field['filter']['settings']['default'];
      }
      // Exposed fields.
      else {
        $filter_value = $this->getOption(['filter', $field_id]);
        // Provide default filters when filter value is empty, list has not been
        // modified and field provides a default.
        if (empty($filter_value) && !$this->isModified() && !empty($field['filter']['settings']['default'])) {
          $filter_value = $field['filter']['settings']['default'];
        }
      }
      /** @var \Drupal\exo_list_builder\Plugin\ExoListFilterInterface $instance */
      $instance = $field['filter']['instance'];
      if (!is_null($filter_value)) {
        if (is_array($filter_value)) {
          $group = NULL;
          switch ($instance->getMultipleJoin($field)) {
            case 'and':
              $group = $query->andConditionGroup();
              break;

            default:
              $group = $query->orConditionGroup();
              break;
          }
          if ($group) {
            foreach ($filter_value as $filter_val) {
              $instance->queryAlter($group, $filter_val, $entity_list, $field);
            }
            $query->condition($group);
          }
        }
        else {
          $instance->queryAlter($query, $filter_value, $entity_list, $field);
        }
      }
    }

    return $query;
  }

  /**
   * Add the sort query.
   */
  protected function addQuerySort(QueryInterface $query) {
    $entity_list = $this->entityList;
    $order = $this->getOption('order');
    $sort = $this->getOption('sort');
    if ($order && $sort) {
      $sort_field = $entity_list->getField($order);
      if (!empty($sort_field['sort_field'])) {
        $order = $sort_field['sort_field'];
      }
    }
    elseif ($order = $entity_list->getSort()) {
      $sort_field = $entity_list->getField($order);
      if (!empty($sort_field['sort_field'])) {
        $order = $sort_field['sort_field'];
        $sort = $sort_field['view']['sort'];
        $this->setOption('order', $order);
        $this->setOption('sort', $sort);
      }
    }
    if ($order && $sort) {
      $query->sort($order, $sort);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function addQueryCondition($field, $value = NULL, $operator = NULL, $langcode = NULL) {
    $this->queryConditions[] = [
      'field' => $field,
      'value' => $value,
      'operator' => $operator,
      'langcode' => $langcode,
    ];
    return $this;
  }

  /**
   * Get the total.
   *
   * @return int
   *   The total results.
   */
  protected function getTotal() {
    if (!isset($this->total)) {
      $this->total = $this->getQuery()->count()->execute();
    }
    return $this->total;
  }

  /**
   * Check if list should be constructed as a form.
   *
   * @return bool
   *   Returns TRUE if list should be constructed as a form.
   */
  protected function isForm() {
    return !empty($this->getExposedFilters()) || !empty($this->entityList->getActions());
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $entity_list = $this->getEntityList();

    if ($entity_list->getSetting('first_page_only_status') && $this->getOption('page') > 0) {
      return [
        '#cache' => [
          'contexts' => $this->getCacheContexts(),
          'tags' => $this->getCacheTags(),
        ],
      ];
    }

    if ($entity_list->getFormat() === 'table' || $this->isForm()) {
      $build = $this->formBuilder->getForm($this);
    }
    else {
      $build = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['exo-reset'],
        ],
      ];
      $build = $this->buildList($build);
    }

    if (!Element::children($build['top'])) {
      $build['top']['#access'] = FALSE;
    }
    else {
      $build['top']['shadow'] = [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#attributes' => [
          'class' => 'exo-list-states--shadow',
        ],
        '#weight' => 100,
      ];
    }
    if (!Element::getVisibleChildren($build['header']['first'])) {
      $build['header']['first']['#access'] = FALSE;
    }
    if (!Element::getVisibleChildren($build['header']['second'])) {
      $build['header']['second']['#access'] = FALSE;
    }
    if (!Element::getVisibleChildren($build['footer'])) {
      $build['footer']['#access'] = FALSE;
    }

    return $build;
  }

  /**
   * Check if the entity list is filtered.
   *
   * @return bool
   *   Returns TRUE if filtered.
   */
  protected function isFiltered() {
    foreach ($this->getFilters() as $field_id => $field) {
      if (!empty($field['filter']['settings']['default'])) {
        return TRUE;
      }
    }
    return !empty($this->getOption('filter'));
  }

  /**
   * Check if the entity list has been modified by the user.
   *
   * This can happen any time the list is submitted.
   *
   * @return bool
   *   Returns TRUE if modified.
   */
  protected function isModified() {
    return !empty($this->getOption('m'));
  }

  /**
   * {@inheritdoc}
   */
  public function buildList(array $build) {
    $entity_list = $this->getEntityList();

    $id = str_replace('_', '-', $entity_list->id());
    $build['#id'] = 'exo-list-' . $id;
    $build['#attributes']['class'][] = 'exo-list';
    $build['#attributes']['class'][] = 'exo-list-' . $id;
    $build['#attached']['library'][] = 'exo_list_builder/list';

    $build['top'] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#weight' => -110,
      '#attributes' => ['class' => ['exo-list-top']],
    ];

    $build['header'] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#weight' => -100,
      '#attributes' => ['class' => ['exo-list-header']],
    ];

    $build['header']['first'] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#weight' => -100,
      '#attributes' => ['class' => ['exo-list-header-first']],
    ];

    $build['header']['second'] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#weight' => -100,
      '#attributes' => ['class' => ['exo-list-header-second']],
    ];

    $format = $this->entityList->getFormat();
    $format_build = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'exo-list-content',
          'exo-list-' . str_replace('_', '-', $format),
        ],
      ],
      '#cache' => [
        'contexts' => $this->getCacheContexts(),
        'tags' => $this->getCacheTags(),
      ],
    ];
    switch ($format) {
      case 'table':
        $format_build['#type'] = 'table';
        $format_build['#header'] = $this->buildHeader();
        $format_build['#title'] = $this->getTitle();
        break;
    }
    $build[$this->entitiesKey] = $format_build;

    $build['footer'] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#weight' => 100,
      '#attributes' => ['class' => ['exo-list-footer']],
    ];

    $entities = $this->load();
    if ($entities) {
      foreach ($entities as $target_entity) {
        if ($row = $this->buildRow($target_entity)) {
          switch ($format) {
            case 'table';
              $build[$this->entitiesKey][$target_entity->id()] = $row;
              break;

            default:
              $build[$this->entitiesKey][$target_entity->id()] = [
                '#type' => 'html_tag',
                '#tag' => 'div',
                '#attributes' => [
                  'class' => [
                    'exo-list-item',
                  ],
                ],
                'row' => $row,
              ];
              break;
          }
        }
      }
      if ($subform = $this->buildFormSort($build)) {
        $build['header']['second']['sort'] = $subform + [
          '#weight' => -10,
        ];
      }
    }
    else {
      $build[$this->entitiesKey] = $this->buildEmpty($build);
    }
    $build[$this->entitiesKey]['#entities'] = $entities;

    $pager = $this->buildFormPager($build);
    $build['header']['second']['pager'] = $pager;
    // Remove pages from header.
    unset($build['header']['second']['pager']['pages']);
    unset($build['header']['second']['pager']['pager_footer']);

    $build['footer']['pager'] = $pager;
    // Remove limit from footer.
    unset($build['footer']['pager']['limit']);
    unset($build['footer']['pager']['pager_header']);

    $found_ops = FALSE;
    $entity_keys = Element::children($build['entities']);
    foreach ($entity_keys as $id) {
      if (!empty($build['entities'][$id]['operations']['data']['#links'])) {
        $found_ops = TRUE;
        break;
      }
    }

    if (!$found_ops) {
      unset($build['entities']['#header']['operations']);
      foreach ($entity_keys as $id) {
        unset($build['entities'][$id]['operations']);
      }
    }

    return $build;
  }

  /**
   * Get cache contexts.
   *
   * @return array
   *   The cache contexts.
   */
  protected function getCacheContexts() {
    return array_merge($this->getEntityList()->getEntityType()->getListCacheContexts(), $this->entityType->getListCacheContexts(), ['url.query_args']);
  }

  /**
   * Get cache tags.
   *
   * @return array
   *   The cache tags.
   */
  protected function getCacheTags() {
    return array_merge($this->getEntityList()->getEntityType()->getListCacheTags(), $this->entityType->getListCacheTags());
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $entity_list = $this->getEntityList();
    $actions = $entity_list->getActions();
    $form = $this->buildList($form);

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Refresh'),
      '#weight' => -200,
      '#attributes' => ['class' => ['hidden']],
    ];

    $format = $this->entityList->getFormat();
    switch ($format) {
      case 'table':
        $form[$this->entitiesKey]['#tableselect'] = !empty($actions);
        break;
    }

    $entities = $form[$this->entitiesKey]['#entities'];

    if ($entities || $this->isFiltered()) {
      // Filter.
      if ($subform = $this->buildFormFilters($form, $form_state)) {
        $form['header']['first']['filters'] = $subform;
      }
    }

    if ($entities) {
      // Columns.
      if ($subform = $this->buildFormColumns($form, $form_state)) {
        $form['header']['first']['columns'] = $subform;
      }

      // Ensure a consistent container for filters/operations in the view
      // header.
      if ($subform = $this->buildFormBatch($form, $form_state)) {
        $form['header']['second']['batch'] = $subform + [
          '#weight' => -100,
        ];
        $form['header']['second']['batch']['#attached']['library'][] = 'exo_list_builder/download';
      }
    }
    else {
      $form[$this->entitiesKey] = $this->buildEmpty($form);
    }

    // Filter overview.
    $filter_overview = $this->buildFormFilterOverview($form, $form_state);
    if (empty($form['header']['second']['batch'])) {
      $form['header']['second']['filter_overview'] = [
        '#weight' => -1000,
      ] + $filter_overview;
    }
    else {
      $form['header']['filter_overview'] = $filter_overview;
    }

    return $form;
  }

  /**
   * Build form pager.
   */
  protected function buildEmpty(array $form) {
    $message = $this->isFiltered() ? $this->getEmptyFilterMessage() : $this->getEmptyMessage();
    return [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#attributes' => [
        'class' => [
          'messages',
          'messages--warning',
          'warning',
        ],
      ],
      'message' => [
        '#markup' => $message,
      ],
    ];
  }

  /**
   * Get the empty message.
   *
   * @return string
   *   The message.
   */
  protected function getEmptyMessage() {
    return $this->t('There are no @label yet.', ['@label' => $this->entityType->getPluralLabel()]);
  }

  /**
   * Get the empty message when no filtered results are found.
   *
   * @return string
   *   The message.
   */
  protected function getEmptyFilterMessage() {
    return $this->t('There are no @label matching the provided conditions.', ['@label' => $this->entityType->getPluralLabel()]);
  }

  /**
   * Build form pager.
   */
  protected function buildFormSort(array $form) {
    $form = [];
    $entity_list = $this->entityList;
    $links = [];
    $order = $this->getOption('order');
    $sort = $this->getOption('sort');
    $sort_fields = $this->getSortFields();
    $default = $this->entityList->getSort();
    if (!$order && !$sort && $default && isset($sort_fields[$default])) {
      $order = $sort_fields[$default]['display_label'];
      $sort = $sort_fields[$default]['view']['sort'];
    }
    foreach ($sort_fields as $field_id => $field) {
      if (!empty($field['view']['sort'])) {
        $asc_url = $this->getOptionsUrl([], [], [
          'order' => $entity_list->getFormat() === 'table' ? $field['display_label'] : $field['id'],
          'sort' => 'asc',
        ]);
        $desc_url = $this->getOptionsUrl([], [], [
          'order' => $entity_list->getFormat() === 'table' ? $field['display_label'] : $field['id'],
          'sort' => 'desc',
        ]);
        $links[$field['id'] . '_asc'] = [
          'title' => $this->icon($field['view']['sort_asc_label'], [
            '@label' => $field['display_label'],
          ])->setIcon('regular-sort-amount-up')->toMarkup(),
          'url' => $asc_url,
        ];
        $links[$field['id'] . '_desc'] = [
          'title' => $this->icon($field['view']['sort_desc_label'], [
            '@label' => $field['display_label'],
          ])->setIcon('regular-sort-amount-down')->toMarkup(),
          'url' => $desc_url,
        ];
        if (($order === $field['display_label'] || $order === $field['id']) && $sort === 'asc') {
          $links = [
            [
              'title' => $this->icon('Sorted by ' . $field['view']['sort_asc_label'], [
                '@label' => $field['display_label'],
              ])->setIcon('regular-sort-amount-up')->toMarkup(),
              'url' => $asc_url,
            ],
          ] + $links;
        }
        elseif (($order === $field['display_label'] || $order === $field['id']) && $sort === 'desc') {
          $links = [
            [
              'title' => $this->icon('Sorted by ' . $field['view']['sort_desc_label'], [
                '@label' => $field['display_label'],
              ])->setIcon('regular-sort-amount-down')->toMarkup(),
              'url' => $desc_url,
            ],
          ] + $links;
        }
      }
    }
    if (!empty($links)) {
      $form = [
        '#type' => 'container',
        '#attributes' => ['class' => ['exo-list-sort']],
      ];
      $form['list'] = [
        '#type' => 'dropbutton',
        '#links' => $links,
      ];
    }

    return $form;
  }

  /**
   * Build form pager.
   */
  protected function buildFormBatch(array $form, FormStateInterface $form_state) {
    $form = [];
    $entity_list = $this->getEntityList();
    if ($actions = $entity_list->getActions()) {
      $form = [
        '#type' => 'container',
        '#attributes' => ['class' => ['exo-list-batch']],
      ];
      $options = [];
      foreach ($actions as $action) {
        $options[$action['id']] = $action['label'];
      }
      if (empty($options)) {
        return [];
      }
      $form['action'] = [
        '#type' => 'select',
        '#options' => ['' => $this->t('- Bulk Actions -')] + $options,
        '#exo_form_default' => TRUE,
      ];
      $form['actions'] = [
        '#type' => 'actions',
        '#states' => [
          '!visible' => [
            ':input[name="action"]' => ['value' => ''],
          ],
        ],
      ];
      $form['actions']['selected'] = [
        '#type' => 'submit',
        '#value' => $this->t('Apply to selected items'),
        '#exo_form_default' => TRUE,
        '#op' => 'action',
        '#submit' => ['::submitBatchForm'],
        '#attributes' => [
          'style' => 'display:none',
        ],
        '#states' => [
          'visible' => [
            ':input[name^="entities["]' => ['checked' => TRUE],
          ],
        ],
      ];
      $form['actions']['all'] = [
        '#type' => 'submit',
        '#value' => $this->t('Apply to all items'),
        '#exo_form_default' => TRUE,
        '#op' => 'action',
        '#submit' => ['::submitBatchForm'],
        '#attributes' => [
          'style' => 'display:none',
        ],
        '#states' => [
          '!visible' => [
            ':input[name^="entities["]' => ['checked' => TRUE],
          ],
        ],
      ];
    }
    return $form;
  }

  /**
   * Build form pager.
   */
  protected function buildFormPager(array $form) {
    $entity_list = $this->getEntityList();
    $form = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#attributes' => ['class' => ['exo-list-pager']],
    ];
    $limit = $this->getOption('limit');
    $total = $this->getTotal();

    if ($limit && $entity_list->getSetting('limit_status')) {
      $form['limit'] = [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#attributes' => ['class' => ['exo-list-pager-limit']],
      ];

      $form['limit']['limit'] = [
        '#type' => 'select',
        '#title' => $this->t('Showing'),
        '#default_value' => $limit,
        '#exo_form_default' => TRUE,
        '#options' => $this->entityList->getLimitOptions(),
      ];

      $form['limit']['limit_submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Go'),
        '#states' => [
          '!visible' => [
            ':input[name="limit"]' => ['value' => $this->getOption('limit')],
          ],
        ],
      ];

      $page = (int) $this->getOption('page') + 1;
      if ($pages = ceil((int) $total / (int) $limit)) {
        $form['pages']['#markup'] = '<div class="exo-list-pages">' . $this->t('Page @page of @pages', [
          '@page' => $page,
          '@pages' => $pages,
        ]) . '</div>';
      }
    }

    if ($entity_list->getSetting('result_status')) {
      $form['total']['#markup'] = '<div class="exo-list-total">' . $this->t('@total items', [
        '@total' => $total,
      ]) . '</div>';
    }

    if ($limit) {
      $form['pager_header'] = $form['pager_footer'] = [
        '#type' => 'pager',
        '#quantity' => 3,
      ];
      $pagerer_header = $this->getEntityList()->getSetting('pagerer_header');
      $pagerer_footer = $this->getEntityList()->getSetting('pagerer_footer');
      if (($pagerer_header || $pagerer_footer) && $this->moduleHandler()->moduleExists('pagerer')) {
        if ($pagerer_header) {
          $form['pager_header'] = [
            '#type' => 'pager',
            '#theme' => 'pagerer',
            '#config' => [
              'preset' => $pagerer_header,
            ],
          ];
          if ($pagerer_header === '_hide') {
            unset($form['pager_header']);
          }
        }
        if ($pagerer_footer && $pagerer_footer !== '_hide') {
          $form['pager_footer'] = [
            '#type' => 'pager',
            '#theme' => 'pagerer',
            '#config' => [
              'preset' => $pagerer_footer,
            ],
          ];
          if ($pagerer_footer === '_hide') {
            unset($form['pager_footer']);
          }
        }
      }
    }

    return $form;
  }

  /**
   * Build form columns.
   */
  protected function buildFormColumns(array $form, FormStateInterface $form_state) {
    $entity_list = $this->getEntityList();
    $all_fields = $entity_list->getFields();
    if (empty($all_fields)) {
      return [];
    }
    $form = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#attributes' => ['class' => ['exo-list-columns']],
    ];
    $form['close'] = [
      '#type' => 'exo_modal_close',
      '#label' => exo_icon()->setIcon('regular-times'),
    ];
    $form['show'] = [
      '#type' => 'table',
      '#header' => [
        'status' => $this->t('Status'),
        'name' => $this->t('Name'),
        'weight' => $this->t('Weight'),
      ],
      '#empty' => $this->t('No fields available.'),
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'weight',
        ],
      ],
    ];
    $shown_fields = $this->getShownFields();
    $weight = 0;
    $fields = array_replace($all_fields, $shown_fields + $all_fields);
    $show = FALSE;
    foreach ($fields as $field_id => $field) {
      if (empty($field['view']['type'])) {
        continue;
      }
      $row = [];
      if (!empty($field['view']['toggle'])) {
        $show = TRUE;
      }
      $enabled = isset($shown_fields[$field_id]);
      $row['#attributes']['class'][] = 'draggable';
      $row['#weight'] = $weight;
      $row['status'] = [
        '#type' => 'checkbox',
        '#default_value' => $enabled,
        '#disabled' => empty($field['view']['toggle']),
      ];
      $row['name'] = [
        '#markup' => '<strong>' . (!empty($field['display_label']) ? $field['display_label'] : $field['label']) . '</strong>',
      ];
      $row['weight'] = [
        '#type' => 'weight',
        '#title' => t('Weight for @title', ['@title' => $field['display_label']]),
        '#title_display' => 'invisible',
        '#default_value' => $weight,
        '#attributes' => ['class' => ['weight']],
      ];
      $form['show'][$field_id] = $row;
      $weight++;
    }

    // We have no toggleable fields. No reason to show this.
    if (!$show) {
      return [];
    }

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Update'),
    ];
    if ($this->getOption('show', FALSE)) {
      $form['actions']['reset'] = [
        '#type' => 'link',
        '#title' => $this->t('Reset'),
        '#url' => $this->getOptionsUrl(['show']),
      ];
    }

    return [
      '#type' => 'exo_modal',
      '#title' => $this->icon('Columns'),
      '#trigger_icon' => 'regular-line-columns',
      '#attributes' => ['class' => ['form-actions']],
      '#trigger_as_button' => TRUE,
      '#modal_settings' => [
        'modal' => [
          'title' => '',
          'right' => 0,
          'openTall' => TRUE,
          'smartActions' => FALSE,
          'closeButton' => FALSE,
          'transitionIn' => 'fadeInRight',
          'transitionOut' => 'fadeOutRight',
        ],
      ],
      '#use_close' => FALSE,
    ] + $form;
  }

  /**
   * Build modal columns.
   */
  protected function buildFormFilters(array $form, FormStateInterface $form_state, array $filters = NULL) {
    $filters = $filters ?: $this->getExposedFilters();
    if (empty($filters)) {
      return [];
    }
    $inline = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#attributes' => [
        'class' => [
          'exo-list-filters-inline',
          'exo-form-inline',
        ],
      ],
      '#parents' => ['filters'],
    ];
    $modal = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#attributes' => ['class' => ['exo-list-filters-modal']],
      '#parents' => ['filters'],
    ];
    $modal['close'] = [
      '#type' => 'exo_modal_close',
      '#label' => exo_icon()->setIcon('regular-times'),
    ];

    $show_modal = FALSE;
    $show_inline = FALSE;
    foreach ($this->buildFormFilterFields($filters, $form_state) as $field_id => $filter_form) {
      $settings = $filters[$field_id]['filter']['settings'];
      if (empty($settings['expose'])) {
        $filter_form['#access'] = FALSE;
      }
      if (!empty($settings['position'])) {
        switch ($settings['position']) {
          case 'header':
            $show_inline = TRUE;
            $inline[$field_id] = $filter_form;
            break;

          default:
            $show_modal = TRUE;
            $modal[$field_id] = $filter_form;
            break;
        }
      }
      else {
        $show_modal = TRUE;
        $modal[$field_id] = $filter_form;
      }
    }

    $modal['actions']['#type'] = 'actions';
    $modal['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Filter'),
    ];

    if ($this->getOption('filter', FALSE)) {
      $modal['actions']['reset'] = [
        '#type' => 'link',
        '#title' => $this->t('Reset'),
        '#url' => $this->getOptionsUrl(['filter']),
      ];
    }

    $form = [];
    if ($show_inline) {
      $form['inline'] = $inline;
      $form['inline']['actions'] = [
        '#type' => 'actions',
        '#attributes' => [
          'class' => ['js-hide'],
        ],
      ];
      $form['inline']['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Apply'),
      ];
    }
    if ($show_modal) {
      $form['modal'] = [
        '#type' => 'exo_modal',
        '#title' => $this->t('Filters'),
        '#trigger_icon' => 'regular-filter',
        '#attributes' => ['class' => ['form-actions']],
        '#trigger_as_button' => TRUE,
        '#modal_settings' => [
          'modal' => [
            'title' => '',
            'right' => 0,
            'openTall' => TRUE,
            'smartActions' => FALSE,
            'closeButton' => FALSE,
            'transitionIn' => 'fadeInRight',
            'transitionOut' => 'fadeOutRight',
          ],
        ],
        '#use_close' => FALSE,
      ] + $modal;
    }
    if (!empty($form)) {
      return [
        '#tree' => TRUE,
      ] + $form;
    }
  }

  /**
   * Build filter fields.
   */
  public function buildFormFilterFields(array $filters, FormStateInterface $form_state) {
    $form = [];
    foreach ($filters as $field_id => $field) {
      if ($field['filter']['instance']) {
        /** @var \Drupal\exo_list_builder\Plugin\ExoListFilterInterface $instance */
        $instance = $field['filter']['instance'];
        $value = $this->getOption([
          'filter',
          $field_id,
        ], ($this->isModified() ? NULL : $field['filter']['settings']['default'] ?? $instance->defaultValue()));
        $form[$field_id] = [];
        $form[$field_id] = $instance->buildForm($form[$field_id], $form_state, $value, $this->entityList, $field);
      }
    }
    return $form;
  }

  /**
   * Build modal columns.
   */
  protected function buildFormFilterOverview(array $form, FormStateInterface $form_state) {
    $form = [];
    $filter_values = $this->getFormFilterOverviewValues($form, $form_state);
    if ($filter_values) {
      $items = [];
      foreach ($filter_values as $filter_id => $filter_value) {
        if ($item = $this->buildFormFilterItem($filter_id, $filter_value)) {
          $items[] = $item;
        }
      }
      if (!empty($items)) {
        $items[] = [
          '#type' => 'link',
          '#title' => $this->t('Reset Filters'),
          '#url' => Url::fromRoute('<current>'),
        ];
        $form['list'] = [
          '#theme' => 'item_list',
          '#title' => $this->t('Filtered By'),
          '#items' => $items,
          '#access' => !empty($items),
          '#prefix' => '<div class="exo-list-filter-overview">',
          '#suffix' => '</div>',
        ];
      }
    }
    return $form;
  }

  /**
   * Build filter overview values.
   */
  protected function getFormFilterOverviewValues(array $form, FormStateInterface $form_state) {
    $filter_values = $this->getOption('filter');
    $filters = $this->getExposedFilters();
    if (!$this->isModified()) {
      foreach ($filters as $field_id => $field) {
        if (!isset($filter_values[$field_id]) && !empty($field['filter']['settings']['default'])) {
          $filter_values[$field_id] = $field['filter']['settings']['default'];
        }
      }
    }
    return $filter_values;
  }

  /**
   * Build filter item.
   */
  protected function buildFormFilterItem($filter_id, $filter_value) {
    $entity_list = $this->getEntityList();
    $field = $this->getExposedFilter($filter_id);
    $value = $filter_value;
    if ($field) {
      $title = $field['display_label'];
      if ($field['filter']['instance']) {
        /** @var \Drupal\exo_list_builder\Plugin\ExoListFilterInterface $instance */
        $instance = $field['filter']['instance'];
        if (is_array($filter_value)) {
          $value = [];
          foreach ($filter_value as $filter_val) {
            $value[] = $instance->toPreview($filter_val, $entity_list, $field);
          }
          $value = implode(', ', $value);
        }
        else {
          $value = $instance->toPreview($filter_value, $entity_list, $field);
        }
        $url = $this->getOptionsUrl([], [$filter_id]);
      }
    }
    else {
      $title = ucwords(str_replace('_', ' ', $filter_id));
      $url = $this->getOptionsUrl();
      $options = $url->getOption('query');
      unset($options[$filter_id]);
      $url->setOption('query', $options);
    }
    return [
      '#type' => 'link',
      '#title' => [
        '#type' => 'inline_template',
        '#template' => '<span class="remove">{{ remove }}</span> <span class="title">{{ title }}</span>{% if value %}: <span class="value">{{ value }}</span>{% endif %}',
        '#context' => [
          'title' => $title,
          'value' => $value,
          'remove' => $this->icon('Remove')->setIcon('regular-times')->setIconOnly(),
        ],
      ],
      '#url' => $url,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $entity_list = $this->getEntityList();

    // Reset options.
    $this->setOptions([]);
    // Limit.
    $this->setOption('limit', $form_state->getValue('limit'));
    // Show.
    if ($show = $form_state->getValue('show')) {
      $fields = $this->getShownFields();
      $show = array_filter($show, function ($item) {
        return !empty($item['status']);
      });
      uasort($show, [
        'Drupal\Component\Utility\SortArray',
        'sortByWeightProperty',
      ]);
      if (array_keys($show) !== array_keys($fields)) {
        $this->setOption('show', array_keys($show));
      }
    }
    // Filters.
    $filters = [];
    foreach ($this->getFilters() as $field_id => $field) {
      if ($field['filter']['instance']) {
        $filter_value = $form_state->getValue(['filters', $field_id]);
        /** @var \Drupal\exo_list_builder\Plugin\ExoListFilterInterface $instance */
        $instance = $field['filter']['instance'];
        if (!$instance->isEmpty($filter_value)) {
          $filters[$field_id] = $instance->toUrlQuery($filter_value, $entity_list, $field);
        }
      }
    }
    if (!empty($filters)) {
      $this->setOption('filter', $filters);
    }

    $url = $this->getOptionsUrl();
    $form_state->setRedirectUrl($url);
  }

  /**
   * {@inheritdoc}
   */
  public function submitBatchForm(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    if (empty($trigger['#op']) || $trigger['#op'] !== 'action') {
      return;
    }
    $entity_list = $this->getEntityList();
    $action = $entity_list->getAvailableActions()[$form_state->getValue('action')];
    $selected = array_filter($form_state->getValue($this->entitiesKey));
    /** @var \Drupal\exo_list_builder\Plugin\ExoListActionInterface $instance */
    $instance = \Drupal::service('plugin.manager.exo_list_action')->createInstance($action['id'], $action['settings']);
    $ids = $instance->getEntityIds($selected, $this);
    $batch_builder = (new BatchBuilder())
      ->setTitle($this->t('Processing Items'))
      ->setFinishCallback([ExoListActionManager::class, 'batchFinish'])
      ->setInitMessage($this->t('Starting item processing.'))
      ->setProgressMessage($this->t('Processed @current out of @total.'))
      ->setErrorMessage($this->t('Item processing has encountered an error.'));
    $do_batch = FALSE;
    foreach ($ids as $entity_id) {
      $do_batch = TRUE;
      $batch_builder->addOperation([ExoListActionManager::class, 'batch'], [
        $action,
        $entity_id,
        $entity_list->id(),
        array_keys($this->getShownFields()),
        isset($selected[$entity_id]),
      ]);
    }
    if ($do_batch) {
      batch_set($batch_builder->toArray());
    }
  }

  /**
   * {@inheritDoc}
   */
  public function buildHeader() {
    foreach ($this->getShownFields() as $field_id => $field) {
      $row[$field_id]['data'] = $field['display_label'];
      if (!empty($field['view']['sort']) && !empty($field['sort_field'])) {
        $row[$field_id] += [
          'specifier' => $field['sort_field'],
          'field' => $field['sort_field'],
          'sort' => $field['view']['sort'],
        ];
      }
      $row[$field_id]['class'][] = Html::getClass('exo-list-builder-field-id--' . $field_id);
      $row[$field_id]['class'][] = Html::getClass('exo-list-builder-field-type--' . $field['view']['type']);
    }
    $row['operations'] = [
      'data' => $this->t('Operations'),
      'class' => [
        'exo-list-builder-field-id--operations',
        'exo-form-table-compact',
      ],
    ];

    if (!$this->getOption('order')) {
      $sort_default = $this->entityList->getSort();
      if (isset($row[$sort_default]['data'])) {
        \Drupal::request()->query->set('order', $row[$sort_default]['data']);
      }
    }

    return $row;
  }

  /**
   * {@inheritDoc}
   */
  public function buildRow(EntityInterface $entity) {
    foreach ($this->getShownFields() as $field_id => $field) {
      $row[$field_id]['data'] = $this->renderField($entity, $field);
      $row[$field_id]['#wrapper_attributes']['class'][] = Html::getClass('exo-list-builder-field-id--' . $field_id);
      $row[$field_id]['#wrapper_attributes']['class'][] = Html::getClass('exo-list-builder-field-type--' . $field['view']['type']);
    }
    if ($this->entityList->showOperations()) {
      $row['operations']['data'] = $this->buildOperations($entity);
      $row['operations']['#wrapper_attributes']['class'][] = 'exo-list-builder-field-id--operations';
      $row['operations']['#wrapper_attributes']['class'][] = 'exo-form-table-compact';
    }
    if ($entity instanceof EntityPublishedInterface) {
      if ($entity->isPublished()) {
        $row['#attributes']['class'][] = 'exo-list-builder--published';
      }
      else {
        $row['#attributes']['class'][] = 'exo-list-builder--unpublished';
      }
    }
    return $row;
  }

  /**
   * Build an individual field's output.
   *
   * @return array
   *   A render array.
   */
  protected function renderField(EntityInterface $entity, array $field) {
    /** @var \Drupal\exo_list_builder\Plugin\ExoListElementInterface $instance */
    $instance = $this->elementManager->createInstance($field['view']['type'], $field['view']['settings']);
    $build = $instance->buildView($entity, $field);
    if (!is_array($build)) {
      $build = [
        '#markup' => $build,
      ];
    }
    if (!empty($field['view']['wrapper'])) {
      $build['#prefix'] = '<' . $field['view']['wrapper'] . '>';
      $build['#suffix'] = '</' . $field['view']['wrapper'] . '>';
    }
    return $build;
  }

  /**
   * Gets this list's default operations.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity the operations are for.
   *
   * @return array
   *   The array structure is identical to the return value of
   *   self::getOperations().
   */
  protected function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);
    if ($entity->access('restore') && $entity->hasLinkTemplate('restore-form')) {
      $operations['restore'] = [
        'title' => $this->t('Restore'),
        'weight' => 98,
        'url' => $this->ensureDestination($entity->toUrl('restore-form')),
      ];
    }
    if ($entity->access('archive') && $entity->hasLinkTemplate('archive-form')) {
      $operations['archive'] = [
        'title' => $this->t('Archive'),
        'weight' => 99,
        'url' => $this->ensureDestination($entity->toUrl('archive-form')),
      ];
    }
    foreach ($operations as &$operation) {
      $operation['title'] = $this->icon($operation['title'])->match(['local_task'], $operation['title']);
    }
    return $operations;
  }

  /**
   * Get fields accounting for shown/hidden.
   *
   * @return array
   *   The fields.
   */
  protected function getShownFields() {
    $fields = $this->getEntityList()->getFields();
    $show = $this->getOption('show');
    if (!empty($show)) {
      $fields = array_replace(array_flip($show), array_intersect_key($fields, array_flip($show)));
    }
    else {
      $fields = array_filter($fields, function ($field) {
        return !empty($field['view']['type']) && !empty($field['view']['show']);
      });
    }
    return $fields;
  }

  /**
   * Get fields accounting for shown/hidden.
   *
   * @return array
   *   The fields.
   */
  protected function getSortFields() {
    $fields = $this->getShownFields();
    $fields = array_filter($fields, function ($field) {
      return !empty($field['view']['sort']);
    });
    return $fields;
  }

  /**
   * {@inheritDoc}
   */
  public function getFilters() {
    if (!isset($this->filters)) {
      $filters = $this->getEntityList()->getFields();
      $filters = array_filter($filters, function ($field) {
        return !empty($field['filter']['type']);
      });
      foreach ($filters as &$field) {
        $field['filter']['instance'] = NULL;
        if ($this->filterManager->hasDefinition($field['filter']['type'])) {
          $field['filter']['instance'] = $this->filterManager->createInstance($field['filter']['type'], $field['filter']['settings']);
        }
      }
      $this->filters = $filters;
    }
    return $this->filters;
  }

  /**
   * Get exposed filter fields.
   *
   * @return array
   *   The exposed filters.
   */
  protected function getExposedFilters() {
    if (!isset($this->exposedFilters)) {
      $filters = [];
      foreach ($this->getFilters() as $field_id => $field) {
        if (empty($field['filter']['settings']['expose']) && empty($field['filter']['settings']['expose_block'])) {
          continue;
        }
        $filters[$field_id] = $field;
      }
      $this->exposedFilters = $filters;
    }
    return $this->exposedFilters;
  }

  /**
   * Get exposed filter field.
   *
   * @return array
   *   The exposed filters.
   */
  protected function getExposedFilter($filter_id) {
    $filters = $this->getExposedFilters();
    return $filters[$filter_id] ?? NULL;
  }

}
