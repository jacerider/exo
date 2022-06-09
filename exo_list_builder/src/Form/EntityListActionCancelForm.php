<?php

namespace Drupal\exo_list_builder\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Url;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Provides an entities deletion confirmation form.
 */
class EntityListActionCancelForm extends EntityConfirmFormBase {

  /**
   * The entity being used by this form.
   *
   * @var \Drupal\exo_list_builder\EntityListInterface
   */
  protected $entity;

  /**
   * The action definition.
   *
   * @var array
   */
  protected $action;

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $exo_entity_list_action = NULL) {
    $actions = $this->entity->getActions();
    if (!isset($actions[$exo_entity_list_action])) {
      throw new AccessDeniedHttpException();
    }
    $this->action = $actions[$exo_entity_list_action];
    $form = parent::buildForm($form, $form_state);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to cancel pending %name actions?', ['%name' => $this->action['label']]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return $this->entity->toUrl();
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelText() {
    return $this->t('Return');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Cancel');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $handler = $this->entity->getHandler();
    if ($queue = $handler->getQueue($this->action['id'])) {
      /** @var \Drupal\exo_list_builder\Plugin\ExoListActionInterface $instance */
      $instance = \Drupal::service('plugin.manager.exo_list_action')->createInstance($this->action['id'], $this->action['settings']);

      /** @var \Drupal\exo_list_builder\QueueWorker\ExoListActionProcess $queue_worker */
      $queue_worker = \Drupal::service('plugin.manager.queue_worker')->createInstance('exo_list_action:' . $this->entity->id() . ':' . $instance->getPluginId());
      $queue_worker->deleteContext();
      $queue->deleteQueue();
    }
    $this->messenger()->addMessage(
      $this->t('The pending %action actions have been canceled.', [
        '@label' => $this->entity->label(),
        '%action' => $this->action['label'],
      ])
    );

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
