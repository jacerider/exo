<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentProperty;

use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_icon\ExoIconTranslationTrait;

/**
 * A 'position' adapter for exo components.
 *
 * @ExoComponentProperty(
 *   id = "position",
 *   label = @Translation("Position"),
 * )
 */
class Position extends ClassAttribute {
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
      'top' => $this->icon('Top')->setIcon('regular-arrow-to-top'),
      'left' => $this->icon('Left')->setIcon('regular-arrow-to-left'),
      'right' => $this->icon('Right')->setIcon('regular-arrow-to-right'),
      'bottom' => $this->icon('Bottom')->setIcon('regular-arrow-to-bottom'),
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
