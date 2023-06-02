<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentProperty;

use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_icon\ExoIconTranslationTrait;

/**
 * A 'position' adapter for exo components.
 *
 * @ExoComponentProperty(
 *   id = "position_vertical",
 *   label = @Translation("Position: Vertical"),
 * )
 */
class PositionVertical extends ClassAttribute {
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
      'center' => $this->icon('Center')->setIcon('regular-compress-arrows-alt'),
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
