<?php

namespace Drupal\exo_list_builder_webform;

use Drupal\Core\Entity\EntityInterface;
use Drupal\exo_icon\ExoIconTranslationTrait;
use Drupal\exo_list_builder\ExoListBuilderConfig;
use Drupal\webform\Utility\WebformDialogHelper;

/**
 * Provides a list builder for config entities.
 */
class ExoListBuilderConfigWebform extends ExoListBuilderConfig {
  use ExoIconTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = parent::render();
    $build['#attached']['library'][] = 'webform/webform.ajax';
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    /** @var \Drupal\webform\WebformInterface $entity */
    $operations = [];
    if ($entity->access('update')) {
      $operations['edit'] = [
        'title' => $this->icon('Build'),
        'url' => $entity->toUrl('edit-form'),
        'weight' => 0,
      ];
    }
    if ($entity->access('submission_page')) {
      $operations['view'] = [
        'title' => $this->t('View'),
        'url' => $entity->toUrl('canonical'),
        'weight' => 10,
      ];
    }
    if ($entity->access('test')) {
      $operations['test'] = [
        'title' => $this->t('Test'),
        'url' => $entity->toUrl('canonical'),
        'weight' => 20,
      ];
    }
    if ($entity->access('submission_view_any') && !$entity->isResultsDisabled()) {
      $operations['results'] = [
        'title' => $this->t('Results'),
        'url' => $entity->toUrl('results-submissions'),
        'weight' => 30,
      ];
    }
    if ($entity->access('update')) {
      $operations['settings'] = [
        'title' => $this->t('Settings'),
        'url' => $entity->toUrl('settings'),
        'weight' => 40,
      ];
    }
    if ($entity->access('duplicate')) {
      $operations['duplicate'] = [
        'title' => $this->t('Duplicate'),
        'url' => $entity->toUrl('duplicate-form'),
        'attributes' => WebformDialogHelper::getModalDialogAttributes(WebformDialogHelper::DIALOG_NARROW),
        'weight' => 90,
      ];
    }
    if ($entity->access('delete') && $entity->hasLinkTemplate('delete-form')) {
      $operations['delete'] = [
        'title' => $this->t('Delete'),
        'url' => $this->ensureDestination($entity->toUrl('delete-form')),
        'attributes' => WebformDialogHelper::getModalDialogAttributes(WebformDialogHelper::DIALOG_NARROW),
        'weight' => 100,
      ];
    }
    return $operations;
  }

}
