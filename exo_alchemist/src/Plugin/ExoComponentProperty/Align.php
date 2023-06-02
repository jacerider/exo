<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentProperty;

use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_icon\ExoIconTranslationTrait;

/**
 * A 'align' adapter for exo components.
 *
 * @ExoComponentProperty(
 *   id = "align",
 *   label = @Translation("Align"),
 * )
 */
class Align extends ClassAttribute {
  use ExoIconTranslationTrait;

  /**
   * {@inheritdoc}
   */
  protected $type = 'exo_radios';

  /**
   * {@inheritdoc}
   */
  public function getOptions() {
    return [
      'top' => $this->icon('Top')->setIcon('regular-border-top'),
      'bottom' => $this->icon('Bottom')->setIcon('regular-border-bottom'),
      'middle' => $this->icon('Center')->setIcon('regular-border-center-h'),
      'baseline' => $this->icon('Right')->setIcon('regular-border-style-alt'),
      'stretch' => $this->icon('Bottom')->setIcon('regular-border-outer'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['#exo_style'] = 'grid';
    return $form;
  }

}
