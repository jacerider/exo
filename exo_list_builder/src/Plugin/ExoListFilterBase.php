<?php

namespace Drupal\exo_list_builder\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformState;
use Drupal\Core\Render\Element;
use Drupal\exo_icon\ExoIconTranslationTrait;
use Drupal\exo_list_builder\EntityListInterface;

/**
 * Base class for eXo list filters.
 */
abstract class ExoListFilterBase extends PluginBase implements ExoListFilterInterface {
  use ExoIconTranslationTrait;
  use DependencySerializationTrait;

  /**
   * Flag indicating if field supports multiple values.
   *
   * @var bool
   */
  protected $supportsMultiple = FALSE;

  /**
   * The cached list widget.
   *
   * @var \Drupal\exo_list_builder\Plugin\ExoListWidgetInterface
   */
  protected $listWidget;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->setConfiguration($configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $default = ExoListFilterInterface::DEFAULTS;
    if ($this instanceof ExoListFieldValuesElementInterface && $this instanceof ExoListFieldValuesInterface) {
      $default['widget'] = 'textfield';
      $default['widget_settings'] = [];
    }
    return $default;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    $configuration = $this->configuration + $this->defaultConfiguration();
    if ($instance = $this->getListWidgetInstance()) {
      $configuration['widget_settings'] += $instance->getConfiguration();
    }
    return $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultValue() {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, EntityListInterface $entity_list, array $field) {
    $configuration = $this->getConfiguration();
    $form['expose'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Expose in List'),
      '#default_value' => !empty($configuration['expose']),
      '#weight' => -100,
    ];
    $form['position'] = [
      '#type' => 'select',
      '#title' => $this->t('Position'),
      '#options' => [
        'header' => $this->t('Header'),
        'modal' => $this->t('Modal'),
      ],
      '#default_value' => $configuration['position'] ?: 'header',
      '#states' => [
        'visible' => [
          ':input[name="fields[' . $field['id'] . '][filter][settings][expose]"]' => ['checked' => TRUE],
        ],
      ],
      '#weight' => -90,
    ];
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#description' => $this->t('If empty, will use field label.'),
      '#default_value' => $configuration['label'],
      '#states' => [
        'visible' => [
          ':input[name="fields[' . $field['id'] . '][filter][settings][expose]"]' => ['checked' => TRUE],
        ],
      ],
      '#weight' => -90,
    ];
    $form['expose_block'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Expose in Block'),
      '#default_value' => !empty($configuration['expose_block']),
      '#states' => [
        'visible' => [
          ':input[name="settings[block_status]"]' => ['checked' => TRUE],
        ],
      ],
      '#weight' => -80,
    ];
    $form['allow_zero'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow 0 (zero) values'),
      '#default_value' => $configuration['allow_zero'],
      '#weight' => -70,
    ];
    if ($this->supportsMultiple) {
      $form['multiple'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Allow multiple values'),
        '#id' => $form['#id'] . '--multiple',
        '#default_value' => $configuration['multiple'],
        '#ajax' => [
          'method' => 'replace',
          'wrapper' => $form['#id'] . '--default',
          'callback' => [__CLASS__, 'ajaxReplaceFilterCallback'],
        ],
        '#weight' => -70,
      ];
      $form['multiple_join'] = [
        '#type' => 'radios',
        '#title' => $this->t('Join'),
        '#options' => ['or' => $this->t('OR'), 'and' => $this->t('AND')],
        '#default_value' => $configuration['multiple_join'],
        '#states' => [
          'visible' => [
            '#' . $form['#id'] . '--multiple' => ['checked' => TRUE],
          ],
        ],
        '#weight' => -70,
      ];
    }

    $default_status = !empty($configuration['default']['status']);
    $form['default'] = [
      '#type' => $default_status ? 'fieldset' : 'html_tag',
      '#tag' => 'div',
      '#attributes' => [
        'id' => $form['#id'] . '--default',
        'class' => ['exo-form-element'],
      ],
      // '#prefix' => '<div id="' . $form['#id'] . '--default">',
      // '#suffix' => '</div>',
      '#weight' => -60,
    ];

    $form['default']['status'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Default value'),
      '#ajax' => [
        'method' => 'replace',
        'wrapper' => $form['#id'] . '--default',
        'callback' => [__CLASS__, 'ajaxReplaceDefault'],
      ],
      '#default_value' => $default_status,
    ];

    if ($default_status) {
      $form['default']['value'] = [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#title' => $this->t('Default value'),
        '#exo_list_field' => $field,
      ];
      $subform_state = SubformState::createForSubform($form['default']['value'], $form, $form_state);
      $default = $configuration['default']['value'] ?: $this->defaultValue();
      $form['default']['value'] = $this->buildForm($form['default']['value'], $subform_state, $default, $entity_list, $field);
      $form['default']['value'] = $this->buildFormAfter($form['default']['value'], $subform_state, $default, $entity_list, $field);
    }

    if ($instance = $this->getListWidgetInstance()) {
      /** @var \Drupal\exo_list_builder\ExoListManagerInterface $widget_manager */
      $widget_manager = \Drupal::service('plugin.manager.exo_list_widget');
      $options = $widget_manager->getOptions();
      $form['widget'] = [
        '#type' => 'select',
        '#title' => $this->t('Widget'),
        '#default_value' => $configuration['widget'],
        '#options' => $options,
        '#weight' => 10000,
        '#ajax' => [
          'method' => 'replace',
          'wrapper' => $form['#id'] . '--widget-settings',
          'callback' => [__CLASS__, 'ajaxReplaceWidget'],
        ],
        '#states' => [
          'visible' => [
            ':input[name="fields[' . $field['id'] . '][filter][settings][expose]"]' => ['checked' => TRUE],
          ],
        ],
      ];
      $form['widget_settings'] = [
        '#type' => 'container',
        '#title' => $this->t('Widget Settings'),
        '#id' => $form['#id'] . '--widget-settings',
        '#weight' => 10000,
      ];
      $subform_state = SubformState::createForSubform($form['widget_settings'], $form, $form_state);
      $form['widget_settings'] = $instance->buildConfigurationForm($form['widget_settings'], $subform_state, $entity_list, $this, $field);
      if (Element::children($form['widget_settings'])) {
        $form['widget_settings']['#type'] = 'fieldset';
      }
    }

    return $form;
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
  public static function ajaxReplaceFilterCallback(array $form, FormStateInterface $form_state) {
    $parents = $form_state->getTriggeringElement()['#array_parents'];
    array_pop($parents);
    $element = NestedArray::getValue($form, $parents);
    return $element['default'];
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
  public static function ajaxReplaceDefault(array $form, FormStateInterface $form_state) {
    $parents = $form_state->getTriggeringElement()['#array_parents'];
    $parents = array_slice($form_state->getTriggeringElement()['#array_parents'], 0, -2);
    $element = NestedArray::getValue($form, $parents);
    return $element['default'];
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
  public static function ajaxReplaceWidget(array $form, FormStateInterface $form_state) {
    $parents = array_slice($form_state->getTriggeringElement()['#array_parents'], 0, -1);
    $element = NestedArray::getValue($form, $parents);
    return $element['widget_settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array $form, FormStateInterface $form_state) {
    $entity_list = $form_state->get('exo_entity_list');
    if (!empty($form['default']['value'])) {
      $subform_state = SubformState::createForSubform($form['default']['value'], $form, $form_state);
      $this->validateForm($form['default']['value'], $subform_state);
      $default = $this->toUrlQuery($form_state->getValue(['default', 'value'], []), $entity_list, $form['default']['value']['#exo_list_field']);
      if ($default) {
        $form_state->setValue(['default', 'value'], $default);
      }
      else {
        $form_state->unsetValue(['default']);
      }
    }
    if ($this instanceof ExoListFieldValuesElementInterface && $this instanceof ExoListFieldValuesInterface && $form_state->getValue('widget')) {
      /** @var \Drupal\exo_list_builder\ExoListManagerInterface $widget_manager */
      $widget_manager = \Drupal::service('plugin.manager.exo_list_widget');
      /** @var \Drupal\exo_list_builder\Plugin\ExoListWidgetInterface $instance */
      $instance = $widget_manager->createInstance($form_state->getValue('widget'), $form_state->getValue('widget_settings') ?? []);
      $subform_state = SubformState::createForSubform($form['widget_settings'], $form, $form_state);
      $instance->validateConfigurationForm($form['widget_settings'], $subform_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $value, EntityListInterface $entity_list, array $field) {
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function buildFormAfter(array $form, FormStateInterface $form_state, $value, EntityListInterface $entity_list, array $field) {
    if ($this instanceof ExoListFieldValuesElementInterface && $this instanceof ExoListFieldValuesInterface) {
      if ($parents = $this->getValuesParents()) {
        $element = NestedArray::getValue($form, $parents);
        if ($element) {
          if ($instance = $this->getListWidgetInstance()) {
            $instance->alterElement($element, $entity_list, $this, $field);
          }
          NestedArray::setValue($form, $parents, $element);
        }
      }
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getFilteredValueOptions(EntityListInterface $entity_list, array $field, $input = NULL) {
    $options = [];
    if ($this instanceof ExoListFieldValuesInterface) {
      $options = $this->getValueOptions($entity_list, $field, $input);
    }
    if ($instance = $this->getListWidgetInstance()) {
      $instance->alterOptions($options, $entity_list, $this, $field);
    }
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function toUrlQuery(array $raw_value, EntityListInterface $entity_list, array $field) {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function toPreview($value, EntityListInterface $entity_list, array $field) {
    if (is_array($value)) {
      $value = implode(', ', $value);
    }
    return $value;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultValue(EntityListInterface $entity_list, array $field) {
    return !empty($field['filter']['settings']['default']['status']) && !is_null($field['filter']['settings']['default']['value']) ? $field['filter']['settings']['default']['value'] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function allowQueryAlter(&$value, EntityListInterface $entity_list, array $field) {
    return !is_null($value);
  }

  /**
   * {@inheritdoc}
   */
  public function queryAlter($query, $value, EntityListInterface $entity_list, array $field) {
  }

  /**
   * {@inheritdoc}
   */
  public function queryRawAlter(SelectInterface $query, $value, EntityListInterface $entity_list, array $field) {
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty($raw_value) {
    return $this->checkEmpty($raw_value);
  }

  /**
   * {@inheritdoc}
   */
  public function supportsMultiple() {
    return !empty($this->supportsMultiple);
  }

  /**
   * Utility function to check if mixed is empty.
   *
   * @param string|array $value
   *   A value.
   *
   * @return bool
   *   Returns TRUE if empty.
   */
  protected function checkEmpty($value) {
    $configuration = $this->getConfiguration();
    if (is_string($value)) {
      if (!empty($configuration['allow_zero']) && ($value === '0' || $value === 0)) {
        return FALSE;
      }
      $value = [trim($value)];
    }
    if (is_array($value)) {
      if (!empty($configuration['allow_zero'])) {
        foreach ($value as $val) {
          if (is_string($val) && ($val === '0' || $val === 0)) {
            return FALSE;
          }
        }
      }
      $value = array_filter($value);
    }
    return empty($value);
  }

  /**
   * Check if field allows multiple.
   */
  public function allowsMultiple(array $field) {
    return !empty($field['filter']['settings']['multiple']);
  }

  /**
   * Check if field allows multiple.
   */
  public function getMultipleJoin(array $field) {
    return $field['filter']['settings']['multiple_join'] ?: 'or';
  }

  /**
   * {@inheritDoc}
   */
  public function getOptionTotal($value, EntityListInterface $entity_list, array $field) {
    $handler = $entity_list->getHandler();
    $options = $handler->getOption(['filter']);
    $handler->setOption(['filter', $field['id']], $value);
    $total = $handler->getRawTotal();
    $handler->setOption(['filter'], $options);
    return $total;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(array $field) {
    return TRUE;
  }

  /**
   * Get the list widget.
   *
   * @return \Drupal\exo_list_builder\Plugin\ExoListWidgetInterface
   *   The list widget instance.
   */
  protected function getListWidgetInstance() {
    if (!isset($this->listWidget)) {
      $this->listWidget = NULL;
      if ($this instanceof ExoListFieldValuesElementInterface && $this instanceof ExoListFieldValuesInterface) {
        $configuration = $this->configuration + $this->defaultConfiguration();
        $widget = $configuration['widget'] ?? NULL;
        $widget_settings = $configuration['widget_settings'] ?? [];
        if ($widget) {
          /** @var \Drupal\exo_list_builder\ExoListManagerInterface $widget_manager */
          $widget_manager = \Drupal::service('plugin.manager.exo_list_widget');
          /** @var \Drupal\exo_list_builder\Plugin\ExoListWidgetInterface $instance */
          $this->listWidget = $widget_manager->createInstance($widget, $widget_settings);
        }
      }
    }
    return $this->listWidget;
  }

  /**
   * Get entity type manager.
   *
   * @return \Drupal\Core\Entity\EntityTypeManagerInterface
   *   The entity type manager.
   */
  protected function entityTypeManager() {
    if (!isset($this->entityTypeManager)) {
      $this->entityTypeManager = \Drupal::entityTypeManager();
    }
    return $this->entityTypeManager;
  }

}
