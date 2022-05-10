<?php

namespace Drupal\exo_modal\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\RenderElement;
use Drupal\exo\Element\ExoButton;

/**
 * Provides a render element that will create a confirmation modal.
 *
 * @RenderElement("exo_modal_confirm")
 */
class ExoModalConfirm extends RenderElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#title' => '',
      '#description' => '',
      '#action' => '',
      '#process' => [
        [$class, 'processGroup'],
        [$class, 'processExoModalConfirm'],
        [$class, 'processAjaxForm'],
      ],
      '#pre_render' => [
        [$class, 'preRenderGroup'],
      ],
    ];
  }

  /**
   * Processes a exo modal confirm element.
   *
   * @param array $element
   *   An associative array containing the properties and children of the
   *   container.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $complete_form
   *   The complete form structure.
   *
   * @return array
   *   The processed element.
   */
  public static function processExoModalConfirm(array &$element, FormStateInterface $form_state, array &$complete_form) {
    $title = $element['#title'];
    $description = $element['#description'];
    $action = $element['#action'];
    $modal = [
      '#type' => 'exo_modal',
      '#title' => $title,
      '#processed' => NULL,
      '#trigger_as_button' => TRUE,
      '#modal_attributes' => ['class' => ['exo-modal-confirm']],
      '#modal_settings' => [
        'modal' => [
          'title' => '',
          'top' => 0,
          'smartActions' => FALSE,
          'closeButton' => FALSE,
          'transitionIn' => 'fadeInDown',
          'transitionOut' => 'fadeOutUp',
          'nest' => TRUE,
          'appendTo' => 'form',
          'appendToOverlay' => 'form',
          'appendToNavigate' => 'form',
          'appendToClosest' => TRUE,
        ],
      ],
      '#use_close' => FALSE,
    ];
    $modal['#attributes']['class'][] = 'js-form-item';
    $modal['#attributes']['class'][] = 'form-item';
    $modal['#attributes']['class'][] = 'js-form-wrapper';
    $modal['#attributes']['class'][] = 'form-wrapper';
    if ($description) {
      $modal['message'] = [
        '#markup' => '<div class="exo-modal-confirm-message">' . $description . '</div>',
      ];
    }
    $modal['actions'] = [
      '#type' => 'actions',
    ];
    $modal['actions']['action'] = $action;
    $modal['actions']['close'] = [
      '#type' => 'exo_modal_close',
      '#label' => t('Cancel'),
      '#attributes' => [
        'class' => ['button', 'button--secondary'],
      ],
    ];
    $element['modal'] = $modal;
    return $element;
  }

}
