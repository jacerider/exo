<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Element;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_list_builder\EntityListInterface;

/**
 * Defines a eXo list element for rendering a content entity field.
 *
 * @ExoListElement(
 *   id = "daterange",
 *   label = @Translation("Formatted Date"),
 *   description = @Translation("Render the datetime range as formatted date."),
 *   weight = 0,
 *   field_type = {
 *     "daterange",
 *   },
 *   entity_type = {},
 *   bundle = {},
 *   field_name = {},
 *   exclusive = FALSE,
 * )
 */
class Daterange extends Timestamp {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'date_end_format' => 'medium',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, EntityListInterface $entity_list, array $field) {
    $form = parent::buildConfigurationForm($form, $form_state, $entity_list, $field);
    $form['date_format']['#title'] = $this->t('Date start format');
    $form['date_format']['#weight'] = -1;
    $form['date_end_format'] = [
      '#title' => $this->t('Date end format'),
    ] + $form['date_format'];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function viewItem(EntityInterface $entity, FieldItemInterface $field_item, array $field) {
    /** @var \Drupal\Core\Datetime\DrupalDateTime $start_date */
    $start_date = $field_item->start_date;
    /** @var \Drupal\Core\Datetime\DrupalDateTime $end_date */
    $end_date = $field_item->end_date;
    $values = [];
    if ($start_date) {
      $values[] = $this->formatTimestamp($start_date->getTimestamp(), $start_date->getTimezone()->getName(), 'date_format');
    }
    if ($end_date) {
      $values[] = $this->formatTimestamp($end_date->getTimestamp(), $end_date->getTimezone()->getName(), 'date_end_format');
    }
    return implode(' - ', $values);
  }

}
