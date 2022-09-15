<?php

namespace Drupal\exo_list_builder\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\exo_list_builder\EntityListInterface;

/**
 * Base class for eXo list filters.
 */
abstract class ExoListWidgetBase extends PluginBase implements ExoListWidgetInterface {
  use StringTranslationTrait;

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
    $default = ExoListWidgetInterface::DEFAULTS;
    if ($this instanceof ExoListWidgetValuesInterface) {
      $default['facet'] = FALSE;
      $default['options'] = [
        'status' => FALSE,
        'exclude' => [],
        'include' => [],
      ];
    }
    return $default;
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
    $this->configuration = $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    return $this->getPluginDefinition()['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, EntityListInterface $entity_list, ExoListFilterInterface $filter, array $field) {
    $configuration = $this->getConfiguration();

    if ($this instanceof ExoListWidgetValuesInterface) {
      $form['facet'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Facet Options'),
        '#description' => $this->t('If checked, this filter will be used as a facet. Which means the available options will contextually change based on the current list results.'),
        '#default_value' => $configuration['facet'],
      ];
      $options_status = $configuration['options']['status'];
      $form['options'] = [
        '#type' => $options_status ? 'fieldset' : 'html_tag',
        '#tag' => 'div',
        '#attributes' => [
          'id' => $form['#id'] . '--options',
          'class' => ['exo-form-element'],
        ],
      ];
      $form['options']['status'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Include/Exclude Options'),
        '#ajax' => [
          'method' => 'replace',
          'wrapper' => $form['#id'] . '--options',
          'callback' => [__CLASS__, 'ajaxReplaceOptions'],
        ],
        '#default_value' => $options_status,
      ];
      if ($options_status) {
        /** @var \Drupal\exo_list_builder\Plugin\ExoListFieldValuesInterface $filter */
        $options = $filter->getValueOptions($entity_list, $field);
        if (count($options) > 50) {
          $form['options']['exclude'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Exclude'),
            '#description' => $this->t('Comma separated list of values to exclude.'),
            '#options' => $options,
            '#default_value' => implode(', ', $configuration['options']['exclude']),
          ];
          $form['options']['include'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Include'),
            '#description' => $this->t('Comma separated list of values to include.'),
            '#options' => $options,
            '#default_value' => implode(', ', $configuration['options']['include']),
          ];
        }
        else {
          $form['options']['exclude'] = [
            '#type' => 'select',
            '#title' => $this->t('Exclude'),
            '#options' => $options,
            '#default_value' => $configuration['options']['exclude'],
            '#multiple' => TRUE,
          ];
          $form['options']['include'] = [
            '#type' => 'select',
            '#title' => $this->t('Include'),
            '#options' => $options,
            '#default_value' => $configuration['options']['include'],
            '#multiple' => TRUE,
          ];
        }
      }
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array $form, FormStateInterface $form_state) {
    if ($this instanceof ExoListWidgetValuesInterface) {
      $exclude = $form_state->getValue(['options', 'exclude'], '');
      $exclude = array_filter(is_array($exclude) ? $exclude : explode(',', $exclude));
      $exclude = $exclude ? array_combine($exclude, $exclude) : [];
      $form_state->setValue(['options', 'exclude'], $exclude);

      $include = $form_state->getValue(['options', 'include'], '');
      $exclude = array_filter(is_array($include) ? $include : explode(',', $include));
      $include = $include ? array_combine($include, $include) : [];
      $form_state->setValue(['options', 'include'], $include);
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
  public static function ajaxReplaceOptions(array $form, FormStateInterface $form_state) {
    $parents = array_slice($form_state->getTriggeringElement()['#array_parents'], 0, -2);
    $element = NestedArray::getValue($form, $parents);
    return $element['options'];
  }

  /**
   * {@inheritdoc}
   */
  public function alterElement(array &$element, EntityListInterface $entity_list, ExoListFilterInterface $filter, array $field) {
  }

  /**
   * {@inheritdoc}
   */
  public function alterOptions(array &$options, EntityListInterface $entity_list, ExoListFilterInterface $filter, array $field) {
    if ($this instanceof ExoListWidgetValuesInterface) {
      $configuration = $this->getConfiguration();
      // Filter options.
      if (!empty($configuration['options']['exclude'])) {
        $options = array_diff_key($options, $configuration['options']['exclude']);
      }
      if (!empty($configuration['options']['include'])) {
        $options = array_intersect_key($options, $configuration['options']['include']);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function applies(EntityListInterface $exo_list) {
    return TRUE;
  }

}
