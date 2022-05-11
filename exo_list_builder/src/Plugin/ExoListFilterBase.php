<?php

namespace Drupal\exo_list_builder\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformState;
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
    return ExoListFilterInterface::DEFAULTS;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration + $this->defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration + $this->defaultConfiguration();
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
    $form['position'] = [
      '#type' => 'select',
      '#title' => $this->t('Position'),
      '#options' => [
        'modal' => $this->t('Modal'),
        'header' => $this->t('Header'),
      ],
      '#default_value' => $configuration['position'] ?: 'modal',
    ];
    $form['expose'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Expose in List'),
      '#default_value' => !empty($configuration['expose']),
    ];
    $form['expose_block'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Expose in Block'),
      '#default_value' => !empty($configuration['expose_block']),
      '#states' => [
        'visible' => [
          ':input[name="settings[block_status]"]' => ['checked' => TRUE],
        ]
      ],
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
      ];
    }
    $form['default'] = [
      '#type' => 'details',
      '#open' => !empty($configuration['default']),
      '#title' => $this->t('Default value'),
      '#exo_list_field' => $field,
      '#prefix' => '<div id="' . $form['#id'] . '--default">',
      '#suffix' => '</div>',
    ];
    $subform_state = SubformState::createForSubform($form['default'], $form, $form_state);
    $default = $configuration['default'] ?: $this->defaultValue();
    $form['default'] = $this->buildForm($form['default'], $subform_state, $default, $entity_list, $field);
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
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array $form, FormStateInterface $form_state) {
    $entity_list = $form_state->get('exo_entity_list');
    if (!empty($form['default'])) {
      $subform_state = SubformState::createForSubform($form['default'], $form, $form_state);
      $this->validateForm($form['default'], $subform_state);
      $default = $this->toUrlQuery($form_state->getValue('default', []), $entity_list, $form['default']['#exo_list_field']);
      if ($default) {
        $form_state->setValue('default', $default);
      }
      else {
        $form_state->unsetValue('default');
      }
    }
    $multiple = $form_state->getValue('multiple');
    if (empty($multiple)) {
      $form_state->unsetValue('multiple_join');
    }
    $position = $form_state->getValue('position');
    if ($position === 'modal') {
      $form_state->unsetValue('position');
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
  public function queryAlter($query, $value, EntityListInterface $entity_list, array $field) {
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty($raw_value) {
    return empty($raw_value);
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
   * {@inheritdoc}
   */
  public function applies(array $field) {
    return TRUE;
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
