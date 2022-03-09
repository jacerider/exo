<?php

namespace Drupal\exo_list_builder;

use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a list builder the will allow confirmation of empty records.
 */
class ExoListBuilderContentStates extends ExoListBuilderContent {

  /**
   * The message shown when no archived records exist.
   *
   * @var string
   */
  protected $stateEmptyMessage = 'No @state @label exist.';

  /**
   * Get state definitions.
   */
  protected function getStates() {
    return [
      'archive' => [
        'label' => $this->t('Archive'),
        'icon' => 'regular-archive',
        'empty' => $this->stateEmptyMessage,
        'hide_filter' => 'status',
      ],
    ];
  }

  /**
   * Check if we should show archived resources.
   */
  protected function getState() {
    $state = \Drupal::request()->query->get('state');
    $states = $this->getStates();
    if (isset($states[$state])) {
      return [
        'id' => $state,
      ] + $states[$state];
    }
  }

  /**
   * Get the query.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   *   The query.
   */
  protected function getQuery() {
    $query = parent::getQuery();

    if ($state = $this->getState()) {
      $this->alterQueryState($state, $query);
    }
    else {
      $this->alterQuery($query);
    }

    return $query;
  }

  /**
   * Alter the query when not in archive mode.
   *
   * @param \Drupal\Core\Entity\Query\QueryInterface $query
   *   The query.
   */
  protected function alterQuery(QueryInterface $query) {
  }

  /**
   * Alter the query when in archive mode.
   *
   * @param array $state
   *   The state.
   * @param \Drupal\Core\Entity\Query\QueryInterface $query
   *   The query.
   */
  protected function alterQueryState(array $state, QueryInterface $query) {
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $build = parent::buildForm($form, $form_state);

    $build['footer']['first'] = [
      '#type' => 'html_tag',
      '#tag' => 'div',
      '#weight' => -10,
      '#attributes' => [
        'class' => 'exo-list-footer-first',
      ],
    ];
    if (!$this->getState()) {
      foreach ($this->getStates() as $id => $state) {
        $build['footer']['first']['state_' . $id] = [
          '#type' => 'link',
          '#title' => $this->icon($state['label'])->setIcon($state['icon']),
          '#url' => Url::fromRoute('<current>', [], [
            'query' => [
              'state' => $id,
            ],
          ]),
        ];
      }
    }

    return $build;
  }

  /**
   * Build modal columns.
   */
  protected function getFormFilterOverviewValues(array $form, FormStateInterface $form_state) {
    $values = parent::getFormFilterOverviewValues($form, $form_state);
    if ($state = $this->getState()) {
      $values['state'] = $state['label'];
    }
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEmptyMessage() {
    if ($state = $this->getState()) {
      return $this->getEmptyMessageState($state);
    }
    return parent::getEmptyMessage();
  }

  /**
   * The empty message shown when in archive view.
   *
   * @param array $state
   *   The state.
   *
   * @return string
   *   The message.
   */
  protected function getEmptyMessageState(array $state) {
    $message = $state['empty'] ?? $this->stateEmptyMessage;
    $message = str_replace('@state', strtolower($state['label']), $message);
    $message = str_replace('@label', strtolower($this->entityType->getPluralLabel()), $message);
    return $this->t('<div>@message</div>', [
      '@message' => $message,
    ]);
  }

  /**
   * {@inheritDoc}
   */
  protected function getExposedFilters() {
    $fields = parent::getExposedFilters();
    if ($state = $this->getState()) {
      if (!empty($state['hide_filter'])) {
        unset($fields[$state['hide_filter']]);
      }
    }
    return $fields;
  }

}
