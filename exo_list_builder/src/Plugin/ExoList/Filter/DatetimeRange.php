<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Filter;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\exo_list_builder\EntityListInterface;
use Drupal\exo_list_builder\Plugin\ExoListFilterBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a eXo list element for rendering a content entity field.
 *
 * @ExoListFilter(
 *   id = "datetime_range",
 *   label = @Translation("Range"),
 *   description = @Translation("Filter datetime between two dates."),
 *   weight = 0,
 *   field_type = {
 *     "datetime",
 *   },
 *   entity_type = {},
 *   bundle = {},
 *   field_name = {},
 *   exclusive = FALSE,
 * )
 */
class DatetimeRange extends ExoListFilterBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\Core\Datetime\DateFormatterInterface definition.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * LogGeneratorBase constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, DateFormatterInterface $date_formatter) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('date.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultValue() {
    return [
      's' => '',
      'e' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $value, EntityListInterface $entity_list, array $field) {
    $form = parent::buildForm($form, $form_state, $value, $entity_list, $field);
    $form['date'] = [
      '#type' => 'fieldset',
      '#title' => $field['display_label'],
      '#attributes' => ['class' => ['container-inline']],
    ];
    $form['date']['s'] = [
      '#type' => 'date',
      '#title' => $this->t('Start'),
      '#default_value' => !empty($value['s']) ? $value['s'] : NULL,
    ];
    $form['date']['e'] = [
      '#type' => 'date',
      '#title' => $this->t('End'),
      '#default_value' => !empty($value['e']) ? $value['e'] : NULL,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function toUrlQuery(array $raw_value, EntityListInterface $entity_list, array $field) {
    $query = [];
    if (!empty($raw_value['date']['s'])) {
      $query['s'] = $raw_value['date']['s'];
    }
    if (!empty($raw_value['date']['e'])) {
      $query['e'] = $raw_value['date']['e'];
    }
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty($raw_value) {
    return $this->checkEmpty($raw_value['date']['s']) && $this->checkEmpty($raw_value['date']['e']);
  }

  /**
   * {@inheritdoc}
   */
  public function toPreview($value, EntityListInterface $entity_list, array $field) {
    $output = [];
    if (!empty($value['s'])) {
      $date = new DrupalDateTime($value['s']);
      $date->setTimezone(new \DateTimeZone(DateTimeItemInterface::STORAGE_TIMEZONE));
      $output[] = $this->dateFormatter->format($date->getTimestamp(), 'custom', 'n/j/y');
    }
    if (!empty($value['e'])) {
      $date = new DrupalDateTime($value['e']);
      $date->setTimezone(new \DateTimeZone(DateTimeItemInterface::STORAGE_TIMEZONE));
      $output[] = $this->dateFormatter->format($date->getTimestamp(), 'custom', 'n/j/y');
    }
    return implode(' — ', $output);
  }

  /**
   * {@inheritdoc}
   */
  public function queryAlter($query, $value, EntityListInterface $entity_list, array $field) {
    if (!empty($value['s'])) {
      $date = new DrupalDateTime($value['s']);
      $date->setTimezone(new \DateTimeZone(DateTimeItemInterface::STORAGE_TIMEZONE));
      $date = $date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT);
      $query->condition($field['field_name'], $date, '>=');
    }
    if (!empty($value['e'])) {
      $date = new DrupalDateTime($value['e']);
      $date->setTimezone(new \DateTimeZone(DateTimeItemInterface::STORAGE_TIMEZONE));
      $date = $date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT);
      $query->condition($field['field_name'], $date, '<=');
    }
  }

}
