<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentProperty;

use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_icon\ExoIconTranslationTrait;

/**
 * A 'justify' adapter for exo components.
 *
 * @ExoComponentProperty(
 *   id = "justify",
 *   label = @Translation("Justify"),
 * )
 */
class Justify extends ClassAttribute {
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
      'left' => $this->icon('Left')->setIcon('regular-border-left'),
      'center' => $this->icon('Center')->setIcon('regular-border-center-v'),
      'right' => $this->icon('Right')->setIcon('regular-border-right'),
      'justify' => $this->icon('Justify')->setIcon('regular-border-inner'),
      'spaced' => $this->icon('Spaced')->setIcon('regular-border-none'),
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
