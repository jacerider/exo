<?php

namespace Drupal\exo_list_builder\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformState;
use Drupal\exo_icon\ExoIconTranslationTrait;
use Drupal\exo_list_builder\EntityListInterface;

/**
 * Base class for eXo list filters.
 */
abstract class ExoListFilterBase extends PluginBase implements ExoListFilterInterface {
  use ExoIconTranslationTrait;

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
    return [
      'default' => NULL,
      'position' => NULL,
      'expose' => TRUE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
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
      '#title' => $this->t('Expose'),
      '#default_value' => $configuration['expose'] ?: 'modal',
    ];
    $form['default'] = [
      '#type' => 'details',
      '#open' => !empty($configuration['default']),
      '#title' => $this->t('Default value'),
      '#exo_list_field' => $field,
    ];
    $subform_state = SubformState::createForSubform($form['default'], $form, $form_state);
    $default = $configuration['default'] ?: $this->defaultValue();
    $form['default'] = $this->buildForm($form['default'], $subform_state, $default, $entity_list, $field);
    return $form;
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
  public function queryAlter(QueryInterface $query, $value, EntityListInterface $entity_list, array $field) {
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty($raw_value) {
    return empty($raw_value);
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
