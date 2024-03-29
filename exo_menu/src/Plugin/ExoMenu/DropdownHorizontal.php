<?php

namespace Drupal\exo_menu\Plugin\ExoMenu;

use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_menu\Plugin\ExoMenuDropdownBase;

/**
 * Plugin implementation of the 'dropdown_horizontal' eXo menu.
 *
 * @ExoMenu(
 *   id = "dropdown_horizontal",
 *   label = @Translation("Dropdown Horizontal"),
 * )
 */
class DropdownHorizontal extends ExoMenuDropdownBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
      'expandable' => TRUE,
      'unbindFirst' => FALSE,
      'transitionIn' => 'expandInY',
      'transitionOut' => 'expandOutY',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['expandable'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Make sub-menus expandable'),
      '#default_value' => $this->configuration['expandable'],
    ];
    $form['unbindFirst'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Unbind events from first item when expanded'),
      '#default_value' => $this->configuration['unbindFirst'],
    ];

    $form['transitionIn'] = [
      '#type' => 'select',
      '#title' => $this->t('Transition In'),
      '#description' => $this->t('Modal opening default transition.'),
      '#options' => ['' => $this->t('- None -')] + exo_animate_in_options(),
      '#default_value' => $this->configuration['transitionIn'],
    ];

    $form['transitionOut'] = [
      '#type' => 'select',
      '#title' => $this->t('Transition Out'),
      '#description' => $this->t('Modal opening default transition.'),
      '#options' => ['' => $this->t('- None -')] + exo_animate_out_options(),
      '#default_value' => $this->configuration['transitionOut'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareBuild(array $build) {
    $build = parent::prepareBuild($build);
    $build['#attributes']['class'][] = 'exo-menu-dropdown-horizontal';
    $build['#attached']['library'][] = 'exo_menu/dropdown.horizontal';
    return $build;
  }

}
