<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\exo_alchemist\ExoComponentValue;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldFieldableBase;
use Drupal\file\FileInterface;
use Drupal\link\LinkItemInterface;
use Drupal\media\MediaInterface;

/**
 * A 'url' adapter for exo components.
 *
 * @ExoComponentField(
 *   id = "url",
 *   label = @Translation("Url"),
 *   properties = {
 *     "url" = @Translation("The absolute url."),
 *   },
 *   widget = {
 *     "type" = "link_default",
 *   },
 * )
 */
class Url extends ExoComponentFieldFieldableBase {

  /**
   * {@inheritdoc}
   */
  public function validateValue(ExoComponentValue $value) {
    parent::validateValue($value);
    if ($value->has('value')) {
      $value->set('uri', $value->get('value'));
      $value->unset('value');
    }
    if (!$value->has('uri')) {
      throw new PluginException(sprintf('eXo Component Field plugin (%s) requires [default.uri] be set.', $value->getDefinition()->getType()));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getStorageConfig() {
    $field = $this->getFieldDefinition();
    $link_type = LinkItemInterface::LINK_GENERIC;
    if ($type = $field->getAdditionalValue('link_type')) {
      switch ($type) {
        case 'internal':
          $link_type = LinkItemInterface::LINK_INTERNAL;
          break;

        case 'external':
          $link_type = LinkItemInterface::LINK_EXTERNAL;
          break;
      }
    }
    return [
      'type' => 'link',
      'settings' => [
        'link_type' => $link_type,
        'title' => DRUPAL_DISABLED,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getWidgetConfig() {
    if (\Drupal::moduleHandler()->moduleExists('exo_linkit')) {
      return [
        'type' => 'exo_linkit',
        'settings' => [
          'icon' => !empty($this->getFieldDefinition()->getAdditionalValue('link_icon')),
          'target' => TRUE,
          'linkit_profile' => 'exo',
        ],
      ];
    }
    if (\Drupal::moduleHandler()->moduleExists('exo_link')) {
      return [
        'type' => 'exo_link',
        'settings' => [
          'icon' => FALSE,
          'target' => TRUE,
        ],
      ];
    }
    return [
      'type' => 'link_default',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultValue($delta = 0) {
    return [
      'uri' => 'internal:/',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function viewValue(FieldItemInterface $item, $delta, array $contexts) {
    /** @var \Drupal\link\LinkItemInterface $item */
    $value = $item->getValue();
    $url = $item->getUrl()->setAbsolute()->toString();
    // Point media to the actual file.
    if (substr($value['uri'], 0, 13) === 'entity:media/') {
      $media_id = substr($value['uri'], 13);
      $media = \Drupal::entityTypeManager()->getStorage('media')->load($media_id);
      if ($media instanceof MediaInterface) {
        $source_field = $media->getSource()->getSourceFieldDefinition($media->get('bundle')->entity);
        if ($source_field && $media->hasField($source_field->getName()) && $media->get($source_field->getName())->entity instanceof FileInterface) {
          /** @var \Drupal\file\FileInterface $file */
          $file = $media->get($source_field->getName())->entity;
          $url = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
        }
      }
    }
    $value['url'] = $url;
    return $value;
  }

  /**
   * {@inheritdoc}
   */
  public function formAlter(array &$form, FormStateInterface $form_state) {
    foreach (Element::children($form['widget']) as $delta) {
      if (isset($form['widget'][$delta]['options'])) {
        $form['widget'][$delta]['options']['#access'] = FALSE;
      }
    }
  }

}
