<?php

namespace Drupal\exo_icon\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\file\Plugin\Field\FieldFormatter\FileFormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\exo_icon\ExoIconTranslationTrait;

/**
 * Plugin implementation of the 'file_icon' formatter.
 *
 * @FieldFormatter(
 *   id = "file_icon",
 *   label = @Translation("eXo Icon"),
 *   field_types = {
 *     "file"
 *   }
 * )
 */
class FileIconFormatter extends FileFormatterBase {
  use ExoIconTranslationTrait;

  /**
   * The mime manager.
   *
   * @var \Drupal\exo_icon\ExoIconMimeManager
   */
  protected $mimeManager;

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $settings = [
      'title' => '',
      'entity_label' => FALSE,
      'icon' => 'regular-file',
      'package' => 'regular',
      'position' => 'before',
      'target' => '',
      'text_only' => '',
      'icon_only' => '',
    ];
    return $settings + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $title = $this->getSetting('title') ? $this->getSetting('title') : 'Default';
    if ($this->getSetting('entity_label')) {
      $title = t('Entity label');
    }
    $summary[] = t('Link title as @title', ['@title' => $title]);
    if ($package_id = $this->getSetting('package')) {
      $package = \Drupal::service('exo_icon.repository')->getPackages()[$package_id];
      $summary[] = t('Package: @value', ['@value' => $package->label()]);
    }
    if ($position = $this->getSetting('position')) {
      $summary[] = t('Icon position: @value', ['@value' => ucfirst($position)]);
    }
    if ($this->getSetting('text_only')) {
      $summary[] = t('Text only');
    }
    if ($this->getSetting('icon_only')) {
      $summary[] = t('Icon only');
    }
    else {
      if ($this->getSetting('target')) {
        $summary[] = t('Open link in new window');
      }
    }
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);
    $elements['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Link title'),
      '#description' => $this->t('If left empty, the file description will be used.'),
      '#default_value' => $this->getSetting('title'),
    ];

    $elements['entity_label'] = [
      '#type' => 'checkbox',
      '#title' => t('Entity Label as Title'),
      '#default_value' => $this->getSetting('entity_label'),
    ];

    $can_change_icon = \Drupal::currentUser()->hasPermission('administer exo icon');

    $options = [];
    foreach (\Drupal::service('exo_icon.repository')->getPackages() as $exo_icon_package) {
      if ($exo_icon_package->status()) {
        $options[$exo_icon_package->id()] = $exo_icon_package->label();
      }
    }
    $elements['package'] = [
      '#type' => 'select',
      '#title' => t('Package'),
      '#options' => $options,
      '#default_value' => $this->getSetting('package'),
      '#required' => TRUE,
      '#access' => $can_change_icon,
    ];

    $elements['text_only'] = [
      '#type' => 'checkbox',
      '#title' => t('Text only'),
      '#default_value' => $this->getSetting('text_only'),
      '#access' => $can_change_icon,
    ];

    $elements['icon_only'] = [
      '#type' => 'checkbox',
      '#title' => t('Icon only'),
      '#default_value' => $this->getSetting('icon_only'),
      '#access' => $can_change_icon,
    ];

    $elements['target'] = [
      '#type' => 'checkbox',
      '#title' => t('Open link in new window'),
      '#return_value' => '_blank',
      '#default_value' => $this->getSetting('target'),
      '#states' => [
        'invisible' => [
          ':input[name*="text_only"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $elements['position'] = [
      '#type' => 'select',
      '#title' => $this->t('Icon position'),
      '#options' => ['before' => $this->t('Before'), 'after' => $this->t('After')],
      '#default_value' => $this->getSetting('position'),
      '#required' => TRUE,
      '#access' => $can_change_icon,
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $file) {
      $item = $file->_referringItem;
      $url = file_create_url($file->getFileUri());
      $options = [];
      if ($this->getSetting('target')) {
        $options['attributes']['target'] = '_blank';
      }

      if ($this->getSetting('entity_label')) {
        $link_text = $items->getEntity()->label();
      }
      else {
        $link_text = !empty($this->getSetting('title')) ? $this->getSetting('title') : (!empty($item->description) ? $item->description : $item->entity->label());
      }
      if (isset($file->_label)) {
        $link_text = $file->_label;
      }
      if (empty($link_text)) {
        $link_text = $item->getEntity()->label();
      }
      $position = $this->getSetting('position');
      $link_text = $this->icon($link_text)->setIcon($this->mimeManager()->getMimeIcon($file->getMimeType(), $this->getSetting('package')));
      if ($position == 'after') {
        $link_text->setIconAfter();
      }
      if ($this->getSetting('icon_only')) {
        $link_text->setIconOnly();
      }
      if ($this->getSetting('text_only')) {
        $elements[$delta]['#markup'] = $link_text;
      }
      else {
        $elements[$delta] = Link::fromTextAndUrl($link_text, Url::fromUri($url, $options))->toRenderable();
      }
      if ($package = $this->getSetting('package')) {
        $elements[$delta]['#attached']['library'][] = 'exo_icon/icon.' . $package;
      }
      $elements[$delta]['#cache']['tags'] = $file->getCacheTags();
      // Pass field item attributes to the theme function.
      if (isset($item->_attributes)) {
        $elements[$delta] += ['#attributes' => []];
        $elements[$delta]['#attributes'] += $item->_attributes;
        // Unset field item attributes since they have been included in the
        // formatter output and should not be rendered in the field template.
        unset($item->_attributes);
      }
    }

    return $elements;
  }

  /**
   * Returns the eXo icon mime manager.
   *
   * @return \Drupal\exo_icon\ExoIconMimeManager
   *   The mime manager.
   */
  protected function mimeManager() {
    if (!$this->mimeManager) {
      $this->mimeManager = \Drupal::service('exo_icon.mime_manager');
    }
    return $this->mimeManager;
  }

}
