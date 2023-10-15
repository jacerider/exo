<?php

namespace Drupal\exo_link_menu\Plugin\Field\FieldWidget;

use Drupal\exo_link\Plugin\Field\FieldWidget\ExoLinkWidget;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Plugin implementation of the 'link' widget.
 *
 * @FieldWidget(
 *   id = "exo_link_menu",
 *   label = @Translation("Menu Link (with icon)"),
 *   no_ui = FALSE,
 *   field_types = {
 *     "link"
 *   }
 * )
 */
class ExoLinkMenuWidget extends ExoLinkWidget {

  /**
   * The module config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * Constructs a WidgetBase object.
   *
   * @param string $plugin_id
   *   The plugin_id for the widget.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the widget is associated.
   * @param array $settings
   *   The widget settings.
   * @param array $third_party_settings
   *   Any third party settings.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings) {
    $this->config = \Drupal::config('exo_link_menu.config');
    $settings['packages'] = $this->config->get('packages');
    $settings['icon'] = $this->config->get('icon') ?? 1;
    if ($options = $this->config->get('class_list')) {
      $settings['class_list'] = $this->extractAllowedValues($options);
    }
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $element['options']['attributes']['data-icon']['#access'] = \Drupal::currentUser()->hasPermission('use exo link menu');
    return $element;
  }

  /**
   * Recursively clean up options array if no data-icon is set.
   */
  public static function validateIconElement($element, FormStateInterface $form_state, $form) {
    parent::validateIconElement($element, $form_state, $form);
    if ($values = $form_state->getValue('link')) {
      foreach ($values as $value) {
        // Support menu_link_attributes module.
        if ($attributes = $form_state->getValue('attributes')) {
          if (!empty($value['options']['attributes'])) {
            $attributes += $value['options']['attributes'];
            $form_state->setValue('attributes', $attributes);
          }
        }
      }
    }
  }

  /**
   * Extracts the allowed values array from the allowed_values element.
   *
   * @param string $string
   *   The raw string to extract values from.
   *
   * @return array|null
   *   The array of extracted key/value pairs, or NULL if the string is invalid.
   *
   * @see \Drupal\options\Plugin\Field\FieldType\ListItemBase::allowedValuesString()
   */
  protected static function extractAllowedValues($string) {
    $values = [];

    $list = explode("\n", $string);
    $list = array_map('trim', $list);
    $list = array_filter($list, 'strlen');

    $generated_keys = $explicit_keys = FALSE;
    foreach ($list as $position => $text) {
      // Check for an explicit key.
      $matches = [];
      if (preg_match('/(.*)\|(.*)/', $text, $matches)) {
        // Trim key and value to avoid unwanted spaces issues.
        $key = trim($matches[1]);
        $value = trim($matches[2]);
        $explicit_keys = TRUE;
      }
      // Otherwise see if we can use the value as the key.
      elseif (!static::validateAllowedValue($text)) {
        $key = $value = $text;
        $explicit_keys = TRUE;
      }
      else {
        return;
      }

      $values[$key] = $value;
    }

    // We generate keys only if the list contains no explicit key at all.
    if ($explicit_keys && $generated_keys) {
      return;
    }

    return $values;
  }

  /**
   * {@inheritdoc}
   */
  protected static function validateAllowedValue($option) {
    if (mb_strlen($option) > 255) {
      return new TranslatableMarkup('Allowed values list: each key must be a string at most 255 characters long.');
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    // Only make this widget available to menu_link_content.
    return $field_definition->getTargetEntityTypeId() == 'menu_link_content';
  }

}
