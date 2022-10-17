<?php

namespace Drupal\exo_modal\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ExoModalViewsController.
 */
class ExoModalAccountController extends ControllerBase {
  use ExoModalResponseTrait;

  /**
   * Password reset.
   */
  public function password(Request $request) {
    $build = [];
    $build['form'] = $this->formBuilder()->getForm('\Drupal\exo_modal\Form\ExoModalAccountPasswordForm');
    if ($this->isAjax()) {
      return $this->buildModalResponse($request, $build, [
        'modal' => [],
      ]);
    }
    return $build;
  }

  /**
   * Account create.
   */
  public function register(Request $request) {
    $build = [];
    $user = $this->entityTypeManager()->getStorage('user')->create();
    $build['form'] = $this->entityFormBuilder()->getForm($user, 'register', [
      'exo_modal_account' => TRUE,
    ]);
    if ($this->isAjax()) {
      return $this->buildModalResponse($request, $build, [
        'modal' => [],
      ]);
    }
    return $build;
  }

  /**
   * Alter the register form.
   */
  public function registerFormAlter(array &$form, FormStateInterface $form_state) {
    $form['#id'] = 'exo-modal-account-register-form';
    if ($form_state->get('exo_modal_account') && $this->isAjax()) {
      $form['actions']['submit']['#ajax'] = [
        'callback' => [get_class($this), 'registerFormAjaxSubmit'],
        'wrapper' => $form['#id'],
      ];
      $form['actions']['close'] = [
        '#markup' => '<a href="#" data-exo-modal-close>' . $this->t('Never Mind') . '</a>',
        '#weight' => 1000,
      ];
    }
  }

  /**
   * Ajax form submit.
   */
  public static function registerFormAjaxSubmit(array &$form, FormStateInterface $form_state) {
    if ($form_state->hasAnyErrors()) {
      $form['status_messages'] = [
        '#type' => 'status_messages',
        '#weight' => -1000,
      ];
      $form['#sorted'] = FALSE;
      $response = new AjaxResponse();
      $response->addCommand(new ReplaceCommand('#' . $form['#id'], $form));
    }
    else {
      $response = new AjaxResponse();
      if ($destination = \Drupal::destination()->get()) {
        $response->addCommand(new RedirectCommand($destination));
      }
      return $response;
    }
    return $response;
  }

}
