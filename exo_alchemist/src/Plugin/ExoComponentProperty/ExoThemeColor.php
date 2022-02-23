<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentProperty;

use Drupal\Core\Form\FormStateInterface;

/**
 * A 'exo_theme_color' adapter for exo components.
 *
 * @ExoComponentProperty(
 *   id = "exo_theme_color",
 *   label = @Translation("Theme Color"),
 * )
 */
class ExoThemeColor extends ClassAttribute {

  /**
   * Get colors.
   */
  public function getColors() {
    return [
      '_none' => [
        'label' => t('None'),
        'hex' => 'transparent',
      ],
      'white' => [
        'label' => t('White'),
        'hex' => '#fff',
      ],
      'black' => [
        'label' => t('Black'),
        'hex' => '#000',
      ],
    ] + exo_theme_colors() + [
      'success' => [
        'label' => t('Success'),
        'hex' => '#86c13d',
      ],
      'warning' => [
        'label' => t('Warning'),
        'hex' => '#f1ba2e',
      ],
      'alert' => [
        'label' => t('Alert'),
        'hex' => '#e54040',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getOptions() {
    $options = [];
    foreach ($this->getColors() as $key => $color) {
      $options[$key] = '<div class="exo-icon exo-swatch no-pad large exo-swatch-' . str_replace('#', '', $color['hex']) . '" style="background-color:' . $color['hex'] . '"></div>';
    }
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  protected function getHtmlClassName($name, $value) {
    $class_values[] = $this->prefix . '--' . $name . '-theme-' . $value;
    if ($value !== '_none') {
      $options = $this->getColors();
      $hex = $options[$value]['hex'];
      $class_values[] = $this->prefix . '--' . $name . '-' . $this->getContrastColor($hex);
    }
    return $class_values;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['#exo_style'] = 'grid-compact';
    return $form;
  }

  /**
   * Check contacts.
   */
  public function getContrastColor($hexColor) {

    // hexColor RGB.
    $r1 = hexdec(substr($hexColor, 1, 2));
    $g1 = hexdec(substr($hexColor, 3, 2));
    $b1 = hexdec(substr($hexColor, 5, 2));

    // Black RGB.
    $blackColor = "#000000";
    $r2BlackColor = hexdec(substr($blackColor, 1, 2));
    $g2BlackColor = hexdec(substr($blackColor, 3, 2));
    $b2BlackColor = hexdec(substr($blackColor, 5, 2));

    // Calc contrast ratio.
    $l1 = 0.2126 * pow($r1 / 255, 2.2) +
      0.7152 * pow($g1 / 255, 2.2) +
      0.0722 * pow($b1 / 255, 2.2);

    $l2 = 0.2126 * pow($r2BlackColor / 255, 2.2) +
      0.7152 * pow($g2BlackColor / 255, 2.2) +
      0.0722 * pow($b2BlackColor / 255, 2.2);

    $contrastRatio = 0;
    if ($l1 > $l2) {
      $contrastRatio = (int) (($l1 + 0.05) / ($l2 + 0.05));
    }
    else {
      $contrastRatio = (int) (($l2 + 0.05) / ($l1 + 0.05));
    }

    if ($contrastRatio >= 5) {
      return 'light';
    }
    else {
      return 'dark';
    }
  }

}
