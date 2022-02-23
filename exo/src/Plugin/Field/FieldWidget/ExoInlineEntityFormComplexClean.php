<?php

namespace Drupal\exo\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\inline_entity_form\Plugin\Field\FieldWidget\InlineEntityFormComplex;

/**
 * Complex inline widget.
 *
 * @FieldWidget(
 *   id = "exo_inline_entity_form_complex_clean",
 *   label = @Translation("Inline entity form - Complex & Clean"),
 *   field_types = {
 *     "entity_reference",
 *     "entity_reference_revisions",
 *   },
 *   multiple_values = true,
 *   provider = "inline_entity_form"
 * )
 */
class ExoInlineEntityFormComplexClean extends InlineEntityFormComplex {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $defaults = parent::defaultSettings();
    $defaults += [
      'allow_new' => TRUE,
      'allow_existing' => FALSE,
      'match_operator' => 'CONTAINS',
      'hide_new' => FALSE,
    ];

    return $defaults;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    $labels = $this->getEntityTypeLabels();
    $states_prefix = 'fields[' . $this->fieldDefinition->getName() . '][settings_edit_form][settings]';
    $element['allow_new'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow users to add new @label.', ['@label' => $labels['plural']]),
      '#default_value' => $this->getSetting('allow_new'),
    ];
    $element['allow_existing'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow users to add existing @label.', ['@label' => $labels['plural']]),
      '#default_value' => $this->getSetting('allow_existing'),
    ];
    $element['match_operator'] = [
      '#type' => 'select',
      '#title' => $this->t('Autocomplete matching'),
      '#default_value' => $this->getSetting('match_operator'),
      '#options' => $this->getMatchOperatorOptions(),
      '#description' => $this->t('Select the method used to collect autocomplete suggestions. Note that <em>Contains</em> can cause performance issues on sites with thousands of nodes.'),
      '#states' => [
        'visible' => [
          ':input[name="' . $states_prefix . '[allow_existing]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $element['hide_new'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Hide new button'),
      '#default_value' => $this->getSetting('hide_new'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    if ($this->getSetting('hide_new')) {
      $summary[] = $this->t('Hide new button');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    if (!empty($element['form'])) {
      $element['entities']['#access'] = FALSE;
    }
    else {
      $open_form = FALSE;
      foreach (Element::children($element['entities']) as $key) {
        $row = &$element['entities'][$key];
        $row['actions']['ief_entity_edit']['#value'] = $this->t('Manage');
        if (!empty($row['form'])) {
          $open_form = TRUE;
        }
      }
      // When we have an open form, we want to simplify the display and remove
      // all unopened rows.
      if ($open_form) {
        foreach (Element::children($element['entities']) as $key) {
          $row = &$element['entities'][$key];
          if (empty($row['form'])) {
            $row['#access'] = FALSE;
            unset($element['entities'][$key]);
          }
        }
        $element['actions']['#access'] = FALSE;
      }
    }

    if ($this->getSetting('hide_new') && !empty($element['form']['inline_entity_form'])) {
      $element['form']['inline_entity_form']['#process'][] = [get_class($this), 'hideNew'];
    }
    return $element;
  }

  /**
   * Hides cancel button.
   *
   * @param array $element
   *   Form array structure.
   */
  public static function hideNew($element) {
    if (isset($element['actions']['ief_add_save'])) {
      $element['actions']['ief_add_save']['#access'] = FALSE;
    }
    return $element;
  }

}
