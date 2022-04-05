<?php

namespace Drupal\exo_list_builder\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Builds the form to delete eXo Entity List entities.
 */
class EntityListFilterForm extends EntityForm {

  /**
   * The entity being used by this form.
   *
   * @var \Drupal\exo_list_builder\EntityListInterface
   */
  protected $entity;

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
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#submit' => ['::submitForm'],
    ];
    return $actions;
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
