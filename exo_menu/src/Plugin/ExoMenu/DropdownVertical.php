<?php

namespace Drupal\exo_menu\Plugin\ExoMenu;

use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_menu\Plugin\ExoMenuDropdownBase;

/**
 * Plugin implementation of the 'dropdown_vertical' eXo menu.
 *
 * @ExoMenu(
 *   id = "dropdown_vertical",
 *   label = @Translation("Dropdown Vertical"),
 * )
 */
class DropdownVertical extends ExoMenuDropdownBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
      'cloneExpandable' => FALSE,
      'expandActiveTrail' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['cloneExpandable'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Clone expandable links into child'),
      '#description' => $this->t('Will take an expandable link, and place it as the first child so that it is clickable.'),
      '#default_value' => $this->configuration['cloneExpandable'],
    ];
    $form['expandActiveTrail'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Expand active trail'),
      '#description' => $this->t('Will automatically expand items in the active trail.'),
      '#default_value' => $this->configuration['expandActiveTrail'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareBuild(array $build) {
    $build = parent::prepareBuild($build);
    $build['#attributes']['class'][] = 'exo-menu-dropdown-vertical';
    $build['#attached']['library'][] = 'exo_menu/dropdown.vertical';
    return $build;
  }

}
