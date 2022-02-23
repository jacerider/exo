<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentProperty;

use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_icon\ExoIconTranslationTrait;

/**
 * A 'position' adapter for exo components.
 *
 * @ExoComponentProperty(
 *   id = "contrast",
 *   label = @Translation("Contrast"),
 * )
 */
class Contrast extends ClassAttribute {
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
      'dark' => $this->icon('Dark')->setIcon('regular-moon'),
      'light' => $this->icon('Light')->setIcon('regular-sun'),
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

  /**
   * {@inheritdoc}
   */
  protected function getHtmlClassName($name, $value) {
    return $this->prefix . '--' . $name . '-' . $value;
  }

}
