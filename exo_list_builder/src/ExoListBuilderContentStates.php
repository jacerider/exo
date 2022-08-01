<?php

namespace Drupal\exo_list_builder;

use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a list builder the will allow confirmation of empty records.
 */
class ExoListBuilderContentStates extends ExoListBuilderContent implements ExoListBuilderContentStatesInterface {

  /**
   * The default state label.
   *
   * @var string
   */
  protected $stateDefaultLabel = 'List';

  /**
   * The default state icon.
   *
   * @var string
   */
  protected $stateDefaultIcon = 'regular-list-alt';

  /**
   * The message shown when no records exist.
   *
   * @var string
   */
  protected $emptyMessage = 'No @state @label exist.';

  /**
   * The message shown when no state records exist.
   *
   * @var string
   */
  protected $stateEmptyMessage = 'No @state @label exist.';

  /**
   * Flag indicating if filter should be down in overview.
   *
   * @var string
   */
  protected $showInFilterOverview = FALSE;

  /**
   * {@inheritDoc}
   */
  public function getDefaultStateLabel() {
    return $this->stateDefaultLabel;
  }

  /**
   * {@inheritDoc}
   */
  public function getDefaultStateIcon() {
    return $this->stateDefaultIcon;
  }

  /**
   * {@inheritDoc}
   */
  public function getStates() {
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
   * {@inheritDoc}
   */
  public function getState() {
    $state = \Drupal::request()->query->get('state');
    $states = $this->getStates();
    if (isset($states[$state])) {
      return [
        'id' => $state,
      ] + $states[$state];
    }
  }

  /**
   * {@inheritDoc}
   */
  protected function buildQuery() {
    $query = parent::buildQuery();

    if ($state = $this->getState()) {
      $this->alterQueryState($state, $query);
    }
    else {
      $this->alterQuery($query);
    }

    return $query;
  }

  /**
   * Alter the query when not in state mode.
   *
   * @param \Drupal\Core\Entity\Query\QueryInterface $query
   *   The query.
   */
  protected function alterQuery(QueryInterface $query) {
  }

  /**
   * Alter the query when in state mode.
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
    $current_state = $this->getState();

    $url = $this->getOptionsUrl();
    $query = $url->getOption('query');
    unset($query['state']);
    $url->setOption('query', $query);
    $links = [
      'state_default' => [
        '#type' => 'link',
        '#title' => $this->icon($this->getDefaultStateLabel())->setIcon($this->getDefaultStateIcon()),
        '#url' => $url,
        '#attributes' => [
          'class' => [
            'exo-list-states--state',
            'exo-list-states--default',
            (empty($current_state) ? 'exo-list-states--active' : ''),
          ],
        ],
      ]
    ];
    foreach ($this->getStates() as $state_id => $state) {
      $url = $this->getOptionsUrl();
      $query = $url->getOption('query');
      $query['state'] = $state_id;
      $url->setOption('query', $query);
      $links['state_' . $state_id] = [
        '#type' => 'link',
        '#title' => $this->icon($state['label'])->setIcon($state['icon']),
        '#url' => $url,
        '#attributes' => [
          'class' => [
            'exo-list-states--state',
            ($current_state && $current_state['id'] === $state_id) ? 'exo-list-states--active' : '',
          ],
        ],
      ];
    }

    if (count($links) > 1) {
      $build['top']['links'] = [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#attributes' => [
          'class' => 'exo-list-states--links',
        ],
      ] + $links;

      $build['footer']['first'] = [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#weight' => -10,
        '#attributes' => [
          'class' => 'exo-list-footer-first',
        ],
      ] + $links;
    }

    return $build;
  }

  /**
   * Build modal columns.
   */
  protected function getFormFilterOverviewValues(array $form, FormStateInterface $form_state) {
    $values = parent::getFormFilterOverviewValues($form, $form_state);
    if ($this->showInFilterOverview && ($state = $this->getState())) {
      $values['state'] = $state['label'];
    }
    return $values;
  }

  /**
   * Get the empty message.
   *
   * @return string
   *   The message.
   */
  protected function getEmptyMessage() {
    if ($state = $this->getState()) {
      return $this->getEmptyMessageState($state);
    }
    $message = $this->emptyMessage;
    $message = str_replace('@state', strtolower($this->stateDefaultLabel), $message);
    $message = str_replace('@label', strtolower($this->entityType->getPluralLabel()), $message);
    return $this->t('<div>@message</div>', [
      '@message' => $message,
    ]);
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
