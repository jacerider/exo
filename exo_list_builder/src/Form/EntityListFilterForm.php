<?php

namespace Drupal\exo_list_builder\Form;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\exo_list_builder\EntityListInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Builds the form to delete eXo Entity List entities.
 */
class EntityListFilterForm extends FormBase {

  /**
   * The entity being used by this form.
   *
   * @var \Drupal\exo_list_builder\EntityListInterface
   */
  protected $entity;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'entity_list_filter_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, EntityListInterface $entity = NULL) {
    $this->entity = $entity;
    $form = $this->form($form, $form_state);
    $form['#cache'] = [
      'tags' => $this->entity->getHandler()->getCacheTags(),
      'contexts' => $this->entity->getHandler()->getCacheContexts(),
    ];
    $form['#attributes']['class'][] = 'exo-entity-list-filter-form';
    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#submit' => ['::submitForm'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $handler = $this->entity->getHandler();

    $fields = $handler->getFilters();
    $fields = array_filter($fields, function ($field) {
      return !empty($field['filter']['settings']['expose_block']);
    });
    if ($subform = $handler->buildFormFilterFields($fields, $form_state)) {
      $form['filters'] = ['#tree' => TRUE] + $subform;
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $handler = $this->entity->getHandler();
    $handler->submitForm($form, $form_state);
    if ($url = $form_state->getRedirect()) {
      /** @var \Drupal\Core\Url $url */
      $form_state->setRedirectUrl(Url::fromRoute($this->entity->getRouteName(), $url->getRouteParameters(), $url->getOptions()));
    }
  }

}
