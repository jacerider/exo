<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Widget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_icon\ExoIconTranslationTrait;
use Drupal\exo_list_builder\EntityListInterface;
use Drupal\exo_list_builder\Plugin\ExoListFieldPropertyInterface;
use Drupal\exo_list_builder\Plugin\ExoListFilterInterface;
use Drupal\exo_list_builder\Plugin\ExoListWidgetBase;
use Drupal\exo_list_builder\Plugin\ExoListWidgetValuesInterface;

/**
 * Defines a eXo list action for batch operations.
 *
 * @ExoListWidget(
 *   id = "links",
 *   label = @Translation("Links"),
 *   description = @Translation("Links widget."),
 * )
 */
class Links extends ExoListWidgetBase implements ExoListWidgetValuesInterface {
  use ExoIconTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'group' => NULL,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, EntityListInterface $entity_list, ExoListFilterInterface $filter, array $field) {
    $form = parent::buildConfigurationForm($form, $form_state, $entity_list, $filter, $field);
    $configuration = $this->getConfiguration();
    if ($filter instanceof ExoListFieldPropertyInterface) {
      $properties = ['' => $this->t('- None -')] + $filter->getPropertyOptions($field['definition']);
      if (count($properties) > 2) {
        if (empty($configuration['group'])) {
          $configuration['group'] = key($properties);
        }
        $form['group'] = [
          '#type' => 'radios',
          '#title' => $this->t('Group'),
          '#options' => $properties,
          '#default_value' => $configuration['group'],
        ];
        if (count($properties) > 5) {
          $form['group']['#type'] = 'select';
        }
      }
    }
    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function alterElement(array &$element, EntityListInterface $entity_list, ExoListFilterInterface $filter, array $field) {
    $configuration = $this->getConfiguration();
    $options = $filter->getFilteredValueOptions($entity_list, $field);
    $multiple = !empty($field['filter']['settings']['multiple']);
    $current = $entity_list->getHandler()->getOption(['filter', $field['id']]) ?? [];
    $current = is_array($current) ? $current : [$current];
    $items = [];
    if (!empty($configuration['group'])) {
      $groups = [];
      foreach ($options as $value => $group) {
        $groups[$group][$value] = $value;
      }
      ksort($groups);
      $groups = array_filter($groups);
      foreach ($groups as $group => $values) {
        asort($values);
        $list = [
          '#theme' => 'item_list',
          '#title' => $group,
          '#items' => $this->buildLinks($entity_list, $field, $values, $current, $multiple),
        ];

        if (count($groups) > 1) {
          $items[] = $list;
        }
      }
    }
    else {
      $items = $this->buildLinks($entity_list, $field, array_keys($options), $current, $multiple);
    }
    $element = [
      '#theme' => 'item_list',
      '#title' => $element['#title'],
      '#items' => $items,
    ];
  }

  /**
   * Build links.
   */
  protected function buildLinks(EntityListInterface $entity_list, array $field, $values, $current, $multiple) {
    $items = [];
    foreach ($values as $value) {
      $is_current = in_array($value, $current);
      $url_value = $multiple ? array_merge($current, [$value]) : $value;
      if ($is_current) {
        $url_value = $multiple ? array_filter($current, function ($item) use ($value) {
          return $item != $value;
        }) : $value;
      }
      $filters = !empty($url_value) ? [
        $field['id'] => $url_value,
      ] : [];
      $items[] = [
        '#type' => 'link',
        '#title' => $value,
        '#title' => [
          '#type' => 'inline_template',
          '#template' => '<span class="value">{{ value }}</span>{% if remove %} <span class="remove">{{ remove }}</span>{% endif %}',
          '#context' => [
            'value' => $value,
            'remove' => !$is_current ? NULL : $this->icon('Remove')->setIcon('regular-times')->setIconOnly(),
          ],
        ],
        '#url' => $entity_list->toFilteredUrl($filters),
      ];
    }
    return $items;
  }

}
