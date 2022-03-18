<?php

namespace Drupal\exo\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\StringFormatter;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'string' formatter.
 *
 * @FieldFormatter(
 *   id = "exo_string",
 *   label = @Translation("eXo Plain text"),
 *   field_types = {
 *     "string",
 *     "uri",
 *   },
 *   quickedit = {
 *     "editor" = "plain_text"
 *   }
 * )
 */
class ExoStringFormatter extends StringFormatter {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'delimiter' => '',
      'prefix' => '',
      'suffix' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element['delimiter'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Delimiter.'),
      '#description' => $this->t('A character which should be used to separate the items.'),
      '#default_value' => $this->getSetting('delimiter'),
      '#size' => 1,
    ];
    $element['prefix'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Prefix'),
      '#default_value' => $this->getSetting('prefix'),
    ];
    $element['suffix'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Suffix'),
      '#default_value' => $this->getSetting('suffix'),
    ];
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    if ($this->getSetting('delimiter')) {
      $summary[] = $this->t('Delimiter: @delimiter', ['@delimiter' => $this->getSetting('delimiter')]);
    }
    if ($this->getSetting('prefix')) {
      $summary[] = $this->t('Prefix: @prefix', ['@prefix' => $this->getSetting('prefix')]);
    }
    if ($this->getSetting('suffix')) {
      $summary[] = $this->t('Suffix: @suffix', ['@suffix' => $this->getSetting('suffix')]);
    }
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    foreach ($elements as $delta => &$element) {
      $element['#prefix'] = '<span class="field-item">' . $this->getSetting('prefix');
      $element['#suffix'] = '</span>';
      if (!empty($this->getSetting('delimiter')) && count($elements) !== $delta + 1) {
        $element['#suffix'] = $this->getSetting('delimiter') . $element['#suffix'];
      }
      $element['#suffix'] = $this->getSetting('suffix') . $element['#suffix'];
    }

    return $elements;
  }

}
