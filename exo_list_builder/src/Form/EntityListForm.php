<?php

namespace Drupal\exo_list_builder\Form;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformState;
use Drupal\exo_list_builder\ExoListActionManagerInterface;
use Drupal\exo_list_builder\ExoListManagerInterface;
use Psr\Container\ContainerInterface;

/**
 * Class entity list form.
 */
class EntityListForm extends EntityForm {

  /**
   * The entity.
   *
   * @var \Drupal\exo_list_builder\EntityListInterface
   */
  protected $entity;

  /**
   * The entity type bundle info service.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * The entity manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The element manager service.
   *
   * @var \Drupal\exo_list_builder\ExoListManagerInterface
   */
  protected $elementManager;

  /**
   * The filter manager service.
   *
   * @var \Drupal\exo_list_builder\ExoListManagerInterface
   */
  protected $filterManager;

  /**
   * The action manager service.
   *
   * @var \Drupal\exo_list_builder\ExoListActionManagerInterface
   */
  protected $actionManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.bundle.info'),
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.exo_list_element'),
      $container->get('plugin.manager.exo_list_filter'),
      $container->get('plugin.manager.exo_list_action')
    );
  }

  /**
   * Entity list form constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle info service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity manager service.
   * @param \Drupal\exo_list_builder\ExoListManagerInterface $element_manager
   *   The element manager service.
   * @param \Drupal\exo_list_builder\ExoListManagerInterface $filter_manager
   *   The filter manager service.
   * @param \Drupal\exo_list_builder\ExoListActionManagerInterface $action_manager
   *   The action manager service.
   */
  public function __construct(EntityTypeBundleInfoInterface $entity_type_bundle_info, EntityTypeManagerInterface $entity_type_manager, ExoListManagerInterface $element_manager, ExoListManagerInterface $filter_manager, ExoListActionManagerInterface $action_manager) {
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
    $this->entityTypeManager = $entity_type_manager;
    $this->elementManager = $element_manager;
    $this->filterManager = $filter_manager;
    $this->actionManager = $action_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var \Drupal\exo_list_builder\EntityListInterface $exo_entity_list */
    $exo_entity_list = $this->entity;
    $entity_type_id = $exo_entity_list->getTargetEntityTypeId();
    $form_state->set('exo_entity_list', $exo_entity_list);

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $exo_entity_list->label(),
      '#description' => $this->t("Label for the eXo Entity List."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $exo_entity_list->id(),
      '#machine_name' => [
        'exists' => '\Drupal\exo_list_builder\Entity\EntityList::load',
      ],
      '#disabled' => !$exo_entity_list->isNew(),
    ];

    $form['status'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enabled'),
      '#default_value' => $exo_entity_list->status(),
    ];

    $form['target_bundles_container'] = [
      '#type' => 'details',
      '#title' => $this->t('Bundles'),
      '#open' => FALSE,
      '#access' => FALSE,
    ];

    $entity_type = $exo_entity_list->getTargetEntityType();
    $form['target_bundles_container']['target_bundles_include'] = [
      '#type' => 'value',
      '#value' => [$entity_type_id => $entity_type_id],
    ];
    if ($entity_type->hasKey('bundle') && $bundles = $this->entityTypeBundleInfo->getBundleInfo($entity_type->id())) {
      $form['target_bundles_container']['#access'] = TRUE;
      $options = [];
      foreach ($bundles as $bundle_id => $bundle) {
        $options[$bundle_id] = $bundle['label'];
      }
      asort($options);
      $form['target_bundles_container']['target_bundles_include'] = [
        '#type' => 'select',
        '#title' => $this->t('Include Bundles'),
        '#description' => $this->t('If no bundles are select, all bundles will be included.'),
        '#default_value' => $exo_entity_list->getTargetBundleIncludeIds(),
        '#options' => $options,
        '#empty_option' => $this->t('- Select -'),
        '#multiple' => TRUE,
      ];
      $form['target_bundles_container']['target_bundles_exclude'] = [
        '#type' => 'select',
        '#title' => $this->t('Exclude Bundles'),
        '#default_value' => $exo_entity_list->getTargetBundleExcludeIds(),
        '#options' => $options,
        '#empty_option' => $this->t('- Select -'),
        '#multiple' => TRUE,
      ];
    }

    if ($exo_entity_list->allowOverride()) {
      $form['override'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Use as Entity List Builder'),
        '#description' => $this->t('If checked, this entity list will be used as the entity list builder for the entity type.'),
        '#default_value' => $exo_entity_list->isOverride(),
      ];
    }

    $form['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL'),
      '#description' => $this->t('A local URL to use for this entity list. Not needed if relying on the entity list builder.'),
      '#default_value' => $exo_entity_list->getUrl(),
    ];

    $form['limit'] = [
      '#type' => 'select',
      '#title' => $this->t('Items per page'),
      '#options' => [
        0 => $this->t('Show All'),
        10 => 10,
        20 => 20,
        50 => 50,
        100 => 100,
      ],
      '#default_value' => $exo_entity_list->getLimit(),
    ];

    $form['settings'] = [
      '#type' => 'container',
      '#tree' => TRUE,
    ];

    if ($this->moduleHandler->moduleExists('pagerer')) {
      /** @var \Drupal\pagerer\PagererPresetListBuilder $pagerer_preset_list */
      $pagerer_preset_list = $this->entityTypeManager->getListBuilder('pagerer_preset');
      $default_label = (string) $this->t('Default:');
      $replace_label = (string) $this->t('Replace with:');
      $options = [
        $default_label => ['' => $this->t('No - use Drupal core pager')],
        $replace_label => $pagerer_preset_list->listOptions(),
      ];
      $form['settings']['pagerer_header'] = [
        '#type' => 'select',
        '#title' => $this->t('Header pager'),
        '#description' => $this->t("Core pager theme requests can be overridden. Select whether they need to be fulfilled by Drupal core pager, or the Pagerer pager to use."),
        '#options' => $options,
        '#default_value' => $exo_entity_list->getSetting('pagerer_header'),
      ];
      $form['settings']['pagerer_footer'] = [
        '#type' => 'select',
        '#title' => $this->t('Footer pager'),
        '#description' => $this->t("Core pager theme requests can be overridden. Select whether they need to be fulfilled by Drupal core pager, or the Pagerer pager to use."),
        '#options' => $options,
        '#default_value' => $exo_entity_list->getSetting('pagerer_footer'),
      ];
    }

    $this->buildFormActions($form, $form_state);

    $form['fields_container'] = [
      '#type' => 'details',
      '#title' => $this->t('Fields'),
      '#open' => TRUE,
      '#prefix' => '<div id="fields-wrapper">',
      '#suffix' => '</div>',
    ];

    $form['fields_container']['fields'] = [
      '#type' => 'table',
      '#header' => [
        'status' => $this->t('Status'),
        'name' => $this->t('Name'),
        'display_label' => $this->t('Label'),
        'view' => $this->t('View'),
        'filter' => $this->t('Filterable'),
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
      '#prefix' => '<div id="fields-wrapper">',
      '#suffix' => '</div>',
    ];

    $fields = NestedArray::mergeDeep($exo_entity_list->getFields(), $exo_entity_list->getAvailableFields(), $exo_entity_list->getFields());
    foreach ($fields as $field_id => $field) {
      $elements = $this->elementManager->getFieldOptions($field['type'], $exo_entity_list->getTargetEntityTypeId(), $exo_entity_list->getTargetBundleIds(), $field_id);
      $elements = array_filter($elements, function ($element) use ($field) {
        /** @var \Drupal\exo_list_builder\Plugin\ExoListElementInterface $instance */
        $instance = $this->elementManager->createInstance($element);
        return $instance->applies($field);
      }, ARRAY_FILTER_USE_KEY);
      $filters = $this->filterManager->getFieldOptions($field['type'], $exo_entity_list->getTargetEntityTypeId(), $exo_entity_list->getTargetBundleIds(), $field_id);
      $filters = array_filter($filters, function ($filter) use ($field) {
        /** @var \Drupal\exo_list_builder\Plugin\ExoListFilterInterface $instance */
        $instance = $this->filterManager->createInstance($filter);
        return $instance->applies($field);
      }, ARRAY_FILTER_USE_KEY);
      $row = [];
      $enabled = $exo_entity_list->hasField($field_id);
      $weight = $enabled ? $field['weight'] : 0;
      $row['#attributes']['class'][] = 'draggable';
      $row['#weight'] = $weight;
      $row['status'] = [
        '#type' => 'checkbox',
        '#default_value' => $enabled,
      ];
      $states = [
        'visible' => [
          ':input[name="fields[' . $field_id . '][status]"]' => ['checked' => TRUE],
        ],
      ];
      $row['name'] = [
        '#markup' => '<strong>' . $field['label'] . '</strong><br><small>' . $this->t('Name: %value', [
          '%value' => $field_id,
        ]) . '</small>',
      ];
      if (!empty($field['definition'])) {
        $row['name']['#markup'] .= '<br><small>' . $this->t('Type: %value', [
          '%value' => $field['definition']->getType(),
        ]) . '</small>';
      }
      $row['display_label'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Label'),
        '#default_value' => $field['display_label'],
        '#states' => $states,
      ];

      $view_id = Html::getId('fields-wrapper-view-' . $field_id);
      $row['view'] = [
        '#type' => 'container',
        '#states' => $states,
        '#id' => $view_id,
      ];
      $view_type = $this->getElementPropertyValue([
        'fields',
        $field_id,
        'view',
        'type',
      ], $form_state, $field['view']['type']);
      $row['view']['type'] = [
        '#type' => 'select',
        '#options' => ['' => $this->t('- Hidden -')] + $elements,
        '#default_value' => $view_type,
        '#ajax' => [
          'event' => 'change',
          'method' => 'replace',
          'wrapper' => $view_id,
          'callback' => [__CLASS__, 'ajaxReplaceFieldsViewCallback'],
        ],
      ];
      $row['view']['options'] = [
        '#type' => 'details',
        '#title' => $this->t('Options'),
        '#access' => !empty($view_type),
        '#states' => [
          '!visible' => [
            ':input[name="fields[' . $field_id . '][view][type]"]' => ['value' => ''],
          ],
        ],
      ];
      $row['view']['options']['toggle'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Allow show/hide'),
        '#default_value' => $field['view']['toggle'],
      ];
      $row['view']['options']['show'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Show by default'),
        '#default_value' => $field['view']['show'],
        '#states' => [
          'visible' => [
            ':input[name="fields[' . $field_id . '][view][options][toggle]"]' => ['checked' => TRUE],
          ],
        ],
      ];
      if (!empty($field['sort_field'])) {
        $row['view']['options']['sort'] = [
          '#type' => 'details',
          '#title' => $this->t('Sort'),
        ];
        $row['view']['options']['sort']['sort'] = [
          '#type' => 'select',
          '#title' => $this->t('Sort'),
          '#parents' => [
            'fields',
            $field_id,
            'view',
            'options',
            'sort',
          ],
          '#options' => [
            '' => $this->t('- None -'),
            'asc' => $this->t('Ascending'),
            'desc' => $this->t('Descending'),
          ],
          '#empty_option' => $this->t('- None -'),
          '#default_value' => $field['view']['sort'],
        ];
        $radio_id = Html::getUniqueId('edit-default-' . $field_id);
        $row['view']['options']['sort']['sort_default'] = [
          '#type' => 'radio',
          '#title' => $this->t('Sort default'),
          '#default_value' => $exo_entity_list->getSort() === $field_id ? $field_id : NULL,
          '#return_value' => $field_id,
          '#parents' => ['sort'],
          '#id' => $radio_id,
          '#attributes' => ['id' => $radio_id],
        ];
      }
      $row['view']['options']['wrapper'] = [
        '#type' => 'select',
        '#title' => $this->t('Wrapping element'),
        '#options' => [
          '' => $this->t('- None -'),
          'small' => '<small>',
          'strong' => '<strong>',
          'em' => '<em>',
        ],
        '#default_value' => $field['view']['wrapper'],
      ];
      $row['view']['settings'] = [
        '#type' => 'details',
        '#title' => $this->t('Settings'),
        '#id' => Html::getId('entity-list-view-settings-' . $field_id),
        '#access' => !empty($view_type),
        '#states' => [
          '!visible' => [
            ':input[name="fields[' . $field_id . '][view][type]"]' => ['value' => ''],
          ],
        ],
      ];
      if (!empty($view_type) && $this->elementManager->hasDefinition($view_type)) {
        /** @var \Drupal\exo_list_builder\Plugin\ExoListElementInterface $instance */
        $instance = $this->elementManager->createInstance($view_type, $field['view']['settings']);
        $subform_state = SubformState::createForSubform($row['view']['settings'], $form, $form_state);
        $row['view']['settings'] = $instance->buildConfigurationForm($row['view']['settings'], $subform_state, $exo_entity_list, $field);
      }

      $filter_id = Html::getId('fields-wrapper-filter-' . $field_id);
      $row['filter'] = [
        '#type' => 'container',
        '#id' => $filter_id,
        '#states' => $states,
      ];
      $filter_type = $this->getElementPropertyValue([
        'fields',
        $field_id,
        'filter',
        'type',
      ], $form_state, $field['filter']['type']);
      $row['filter']['type'] = [
        '#type' => 'select',
        '#options' => ['' => $this->t('- None -')] + $filters,
        '#default_value' => $filter_type,
        '#ajax' => [
          'event' => 'change',
          'method' => 'replace',
          'wrapper' => $filter_id,
          'callback' => [__CLASS__, 'ajaxReplaceFieldsFilterCallback'],
        ],
      ];
      $row['filter']['settings'] = [
        '#type' => 'details',
        '#title' => $this->t('Settings'),
        '#id' => Html::getId('entity-list-filter-settings-' . $field_id),
        '#access' => !empty($filter_type),
        '#states' => [
          '!visible' => [
            ':input[name="fields[' . $field_id . '][filter][type]"]' => ['value' => ''],
          ],
        ],
      ];
      if (!empty($filter_type) && $this->filterManager->hasDefinition($filter_type)) {
        /** @var \Drupal\exo_list_builder\Plugin\ExoListFilterInterface $instance */
        $instance = $this->filterManager->createInstance($filter_type, $field['filter']['settings']);
        $subform_state = SubformState::createForSubform($row['filter']['settings'], $form, $form_state);
        $row['filter']['settings'] = $instance->buildConfigurationForm($row['filter']['settings'], $subform_state, $exo_entity_list, $field);
      }

      $row['weight'] = [
        '#type' => 'weight',
        '#title' => t('Weight for @title', [
          '@title' => $field['label'],
        ]),
        '#title_display' => 'invisible',
        '#default_value' => $weight,
        '#attributes' => ['class' => ['weight']],
      ];
      $form['fields_container']['fields'][$field_id] = $row;
    }

    return $form;
  }

  /**
   * Build actions.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  protected function buildFormActions(array &$form, FormStateInterface $form_state) {
    $exo_entity_list = $this->entity;

    $actions = $this->getElementPropertyValue(['actions'], $form_state, $exo_entity_list->getActions());

    $form['actions_container'] = [
      '#type' => 'details',
      '#title' => $this->t('Actions'),
      '#open' => FALSE,
      '#prefix' => '<div id="actions-wrapper">',
      '#suffix' => '</div>',
    ];
    $form['actions_container']['actions'] = [
      '#type' => 'table',
      '#header' => [
        'status' => $this->t('Status'),
        'name' => $this->t('Name'),
        'settings' => '',
      ],
      '#empty' => $this->t('No actions available.'),
    ];
    foreach ($exo_entity_list->getAvailableActions() as $action_id => $action) {
      $enabled = isset($actions[$action_id]);
      $row = [];
      $row['status'] = [
        '#type' => 'checkbox',
        '#default_value' => $enabled,
        '#ajax' => [
          'event' => 'change',
          'method' => 'replace',
          'wrapper' => 'actions-wrapper',
          'callback' => [__CLASS__, 'ajaxReplaceActionsCallback'],
        ],
      ];
      $row['name'] = [
        '#markup' => '<strong>' . $action['label'] . '</strong>',
      ];
      $row['settings'] = [];
      if ($enabled) {
        $row['settings'] = [
          '#type' => 'container',
          '#tree' => TRUE,
        ];
        /** @var \Drupal\exo_list_builder\Plugin\ExoListActionInterface $instance */
        $instance = $this->actionManager->createInstance($action_id, $action['settings']);
        $subform_state = SubformState::createForSubform($row['settings'], $form, $form_state);
        $row['settings'] = $instance->buildConfigurationForm($row['settings'], $subform_state, $exo_entity_list, $action);
      }
      $form['actions_container']['actions'][$action_id] = $row;
    }
  }

  /**
   * Ajax replace callback.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   The fields form.
   */
  public static function ajaxReplaceActionsCallback(array $form, FormStateInterface $form_state) {
    return $form['actions_container'];
  }

  /**
   * Ajax replace callback.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   The fields form.
   */
  public static function ajaxReplaceFieldsFilterCallback(array $form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $parents = $trigger['#array_parents'];
    array_pop($parents);
    $view = NestedArray::getValue($form, $parents);
    return $view;
  }

  /**
   * Ajax replace callback.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   The fields form.
   */
  public static function ajaxReplaceFieldsViewCallback(array $form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $parents = $trigger['#array_parents'];
    array_pop($parents);
    $view = NestedArray::getValue($form, $parents);
    return $view;
  }

  /**
   * Get element property value.
   *
   * @param array|string $property
   *   The property.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   * @param mixed $default
   *   The default value.
   *
   * @return array|mixed|null
   *   The property value.
   */
  protected function getElementPropertyValue($property, FormStateInterface $form_state, $default = '') {
    return $form_state->hasValue($property)
      ? $form_state->getValue($property)
      : $default;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    // Clean settings.
    $form_state->setValue('settings', array_filter($form_state->getValue('settings', [])));

    // Url validation.
    $url = $form_state->getValue('url');
    if ($url) {
      if (substr($url, 0, 1) !== '/') {
        $form_state->setError($form['url'], $this->t('The url must be a local URL and start with a slash.'));
      }
    }

    $fields = $form_state->getValue('fields');
    foreach ($fields as $field_id => &$field) {
      if (empty($field['status'])) {
        unset($fields[$field_id]);
      }
      else {
        unset($field['status']);
        $field['view'] += $field['view']['options'];
        unset($field['view']['options']);
        if (empty($field['view']['toggle'])) {
          $field['view']['show'] = TRUE;
        }
        if (!empty($field['view']['type']) && $this->elementManager->hasDefinition($field['view']['type'])) {
          /** @var \Drupal\exo_list_builder\Plugin\ExoListElementInterface $instance */
          $instance = $this->elementManager->createInstance($field['view']['type'], $field['view']['settings'] ?? []);
          $subform_state = SubformState::createForSubform($form['fields_container']['fields'][$field_id]['view']['settings'], $form, $form_state);
          $instance->validateConfigurationForm($form['fields_container']['fields'][$field_id]['view']['settings'], $subform_state);
          $field['view']['settings'] = $subform_state->getValues();
        }
        if (!empty($field['filter']['type']) && $this->filterManager->hasDefinition($field['filter']['type'])) {
          /** @var \Drupal\exo_list_builder\Plugin\ExoListFilterInterface $instance */
          $instance = $this->filterManager->createInstance($field['filter']['type'], $field['filter']['settings'] ?? []);
          $subform_state = SubformState::createForSubform($form['fields_container']['fields'][$field_id]['filter']['settings'], $form, $form_state);
          $instance->validateConfigurationForm($form['fields_container']['fields'][$field_id]['filter']['settings'], $subform_state);
          $field['filter']['settings'] = $subform_state->getValues();
        }
      }
    }
    $form_state->setValue('fields', $fields);

    $actions = $form_state->getValue('actions');
    foreach ($actions as $action_id => &$action) {
      if (empty($action['status'])) {
        unset($actions[$action_id]);
      }
      else {
        unset($action['status']);
      }
    }
    $form_state->setValue('actions', $actions);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $exo_entity_list = $this->entity;
    $status = $exo_entity_list->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label eXo Entity List.', [
          '%label' => $exo_entity_list->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label eXo Entity List.', [
          '%label' => $exo_entity_list->label(),
        ]));
    }

    $form_state->setRedirectUrl($exo_entity_list->toUrl('edit-form'));
  }

}