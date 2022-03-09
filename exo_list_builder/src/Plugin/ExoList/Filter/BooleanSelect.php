<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Filter;

use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_list_builder\EntityListInterface;
use Drupal\exo_list_builder\Plugin\ExoListFieldValuesInterface;
use Drupal\exo_list_builder\Plugin\ExoListFilterBase;

/**
 * Defines a eXo list element for rendering a content entity field.
 *
 * @ExoListFilter(
 *   id = "boolean_select",
 *   label = @Translation("Select"),
 *   description = @Translation("Filter boolean with a select element."),
 *   weight = 0,
 *   field_type = {
 *     "boolean",
 *   },
 *   entity_type = {},
 *   bundle = {},
 *   field_name = {},
 *   exclusive = FALSE,
 * )
 */
class BooleanSelect extends ExoListFilterBase implements ExoListFieldValuesInterface {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'format' => 'default',
      'format_custom_false' => '',
      'format_custom_true' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, EntityListInterface $entity_list, array $field) {
    $form = parent::buildConfigurationForm($form, $form_state, $entity_list, $field);
    $configuration = $this->getConfiguration();
    $formats = [];
    foreach ($this->getOutputFormats($field) as $format_name => $format) {
      if (is_array($format)) {
        if ($format_name == 'default') {
          $formats[$format_name] = $this->t('Field settings (@on_label / @off_label)', [
            '@on_label' => $format[0],
            '@off_label' => $format[1],
          ]);
        }
        else {
          $formats[$format_name] = $this->t('@on_label / @off_label', [
            '@on_label' => $format[0],
            '@off_label' => $format[1],
          ]);
        }
      }
      else {
        $formats[$format_name] = $format;
      }
    }
    $form['format'] = [
      '#type' => 'select',
      '#title' => $this->t('Output format'),
      '#default_value' => $configuration['format'],
      '#options' => $formats,
      '#id' => $form['#id'] . '-format',
    ];
    $form['format_custom_true'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Custom output for TRUE'),
      '#default_value' => $configuration['format_custom_true'],
      '#states' => [
        'visible' => [
          '#' . $form['#id'] . '-format' => ['value' => 'custom'],
        ],
      ],
    ];
    $form['format_custom_false'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Custom output for FALSE'),
      '#default_value' => $configuration['format_custom_false'],
      '#states' => [
        'visible' => [
          '#' . $form['#id'] . '-format' => ['value' => 'custom'],
        ],
      ],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultValue() {
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $value, EntityListInterface $entity_list, array $field) {
    $form = parent::buildForm($form, $form_state, $value, $entity_list, $field);
    $form['q'] = [
      '#type' => 'select',
      '#title' => $field['display_label'],
      '#options' => $this->getValueOptions($entity_list, $field),
      '#empty_option' => $this->t('- All -'),
      '#empty_value' => NULL,
      '#default_value' => $value,
    ];
    return $form;
  }

  /**
   * Gets the available format options.
   *
   * @return array|string
   *   A list of output formats. Each entry is keyed by the machine name of the
   *   format. The value is an array, of which the first item is the result for
   *   boolean TRUE, the second is for boolean FALSE. The value can be also an
   *   array, but this is just the case for the custom format.
   */
  protected function getOutputFormats(array $field) {
    $settings = $field['definition']->getFieldStorageDefinition()->getSettings();
    $formats = [
      'default' => [$settings['on_label'], $settings['off_label']],
      'yes-no' => [$this->t('Yes'), $this->t('No')],
      'true-false' => [$this->t('True'), $this->t('False')],
      'on-off' => [$this->t('On'), $this->t('Off')],
      'enabled-disabled' => [$this->t('Enabled'), $this->t('Disabled')],
      'boolean' => [1, 0],
      'unicode-yes-no' => ['✔', '✖'],
      'custom' => $this->t('Custom'),
    ];
    return $formats;
  }

  /**
   * {@inheritdoc}
   */
  public function getValueOptions(EntityListInterface $entity_list, array $field, $input = NULL) {
    $configuration = $this->getConfiguration();
    $formats = $this->getOutputFormats($field);
    $format = $configuration['format'];
    $options = [];
    if (isset($formats[$format])) {
      $options[1] = $formats[$format][0];
      $options[0] = $formats[$format][1];
    }
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty($raw_value) {
    return is_null($raw_value['q']) || $raw_value['q'] === '';
  }

  /**
   * {@inheritdoc}
   */
  public function toUrlQuery(array $raw_value, EntityListInterface $entity_list, array $field) {
    return $raw_value['q'];
  }

  /**
   * {@inheritdoc}
   */
  public function queryAlter($query, $value, EntityListInterface $entity_list, array $field) {
    $query->condition($field['id'], $value, '=');
  }

  /**
   * {@inheritdoc}
   */
  public function toPreview($value, EntityListInterface $entity_list, array $field) {
    $configuration = $this->getConfiguration();
    $formats = $this->getOutputFormats($field);
    $format = $configuration['format'];
    if (is_array($value)) {
      $value = implode(', ', $value);
    }
    return $value ? $formats[$format][0] : $formats[$format][1];
  }

}
