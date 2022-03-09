<?php

namespace Drupal\exo_list_builder;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a list builder the will allow confirmation of empty records.
 */
class ExoListBuilderContentModerated extends ExoListBuilderContent {

  /**
   * The archive title.
   *
   * @var string
   */
  protected $archiveLabel = 'Archive';

  /**
   * The message shown when no archived records exist.
   *
   * @var string
   */
  protected $archiveEmptyMessage = 'No archived @label exist.';

  /**
   * Get the query.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   *   The query.
   */
  protected function getQuery() {
    $query = parent::getQuery();

    // Only show draft/published.
    $states = [
      'draft',
      'published',
    ];
    if ($this->showArchive()) {
      $states = [
        'archived',
      ];
    }
    $query->addTag('exo_entity_list_moderation_state');
    $query->addMetaData('exo_entity_list_moderation_state', $states);

    return $query;
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
    if (!$this->showArchive()) {
      $build['footer']['first']['archive'] = [
        '#type' => 'link',
        '#title' => $this->icon($this->archiveLabel, [
          '@label' => $this->entityType->getPluralLabel(),
        ])->setIcon('regular-archive'),
        '#url' => Url::fromRoute('<current>', [], [
          'query' => [
            'state' => 'archive',
          ],
        ]),
      ];
    }

    return $build;
  }

  /**
   * Build modal columns.
   */
  protected function getFormFilterOverviewValues(array $form, FormStateInterface $form_state) {
    $values = parent::getFormFilterOverviewValues($form, $form_state);
    if ($this->showArchive()) {
      $values['state'] = $this->archiveLabel;
    }
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEmptyMessage() {
    if ($this->showArchive()) {
      return $this->getEmptyMessageArchive();
    }
    return parent::getEmptyMessage();
  }

  /**
   * The empty message shown when in archive view.
   *
   * @return string
   *   The message.
   */
  protected function getEmptyMessageArchive() {
    return $this->t('<div>@message</div>', [
      '@message' => str_replace('@label', strtolower($this->entityType->getPluralLabel()), $this->archiveEmptyMessage),
    ]);
  }

  /**
   * Check if we should show archived resources.
   */
  protected function showArchive() {
    return \Drupal::request()->query->get('state') == 'archive';
  }

}
