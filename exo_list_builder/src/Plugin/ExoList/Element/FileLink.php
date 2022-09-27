<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Element;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\exo_icon\ExoIconMimeManager;
use Drupal\exo_icon\ExoIconTranslationTrait;
use Drupal\exo_list_builder\EntityListInterface;
use Drupal\exo_list_builder\Plugin\ExoListElementContentBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a eXo list element for rendering a content entity field.
 *
 * @ExoListElement(
 *   id = "file_link",
 *   label = @Translation("Download Link"),
 *   description = @Translation("Render the file as a download link."),
 *   weight = 0,
 *   field_type = {
 *     "file",
 *   },
 *   entity_type = {},
 *   bundle = {},
 *   field_name = {},
 *   exclusive = FALSE,
 * )
 */
class FileLink extends ExoListElementContentBase implements ContainerFactoryPluginInterface {
  use ExoIconTranslationTrait;

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\exo_icon\ExoIconMimeManager
   */
  protected $mimeManager;

  /**
   * The file URL generator.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  /**
   * LogGeneratorBase constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\exo_icon\ExoIconMimeManager $mime_manager
   *   The entity type manager service.
   * @param \Drupal\Core\File\FileUrlGeneratorInterface $file_url_generator
   *   The file URL generator.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ExoIconMimeManager $mime_manager, FileUrlGeneratorInterface $file_url_generator) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->mimeManager = $mime_manager;
    $this->fileUrlGenerator = $file_url_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('exo_icon.mime_manager'),
      $container->get('file_url_generator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      // 'link_title' => '',
      'title_type' => 'entity_label',
      'title' => NULL,
      'package' => 'regular',
      'target' => NULL,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, EntityListInterface $entity_list, array $field) {
    $form = parent::buildConfigurationForm($form, $form_state, $entity_list, $field);
    unset($form['link']);

    $configuration = $this->getConfiguration();
    $form['title_type'] = [
      '#type' => 'radios',
      '#title' => $this->t('Title Type'),
      '#id' => $form['#id'] . '--title-type',
      '#options' => [
        'entity_label' => $this->t('Entity Label'),
        'file_name' => $this->t('File Name'),
        'custom' => $this->t('Custom'),
      ],
      '#default_value' => $configuration['title_type'],
    ];
    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Custom Title'),
      '#default_value' => $configuration['title'],
      '#states' => [
        'visible' => [
          '#' . $form['#id'] . '--title-type input' => ['value' => 'custom'],
        ],
      ],
    ];

    $options = [];
    foreach (\Drupal::service('exo_icon.repository')->getPackages() as $exo_icon_package) {
      if ($exo_icon_package->status()) {
        $options[$exo_icon_package->id()] = $exo_icon_package->label();
      }
    }
    $form['package'] = [
      '#type' => 'select',
      '#title' => t('Icon Package'),
      '#options' => $options,
      '#default_value' => $configuration['package'],
      '#empty_option' => $this->t('- No Icon -'),
    ];

    $form['target'] = [
      '#type' => 'checkbox',
      '#title' => t('Open link in new window'),
      '#return_value' => '_blank',
      '#default_value' => $configuration['target'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function viewItem(EntityInterface $entity, FieldItemInterface $field_item, array $field) {
    $configuration = $this->getConfiguration();

    $file = $field_item->entity;
    $url = $this->fileUrlGenerator->generate($file->getFileUri());
    switch ($configuration['title_type']) {
      case 'file_name':
        $link_title = $file->label();
        break;

      case 'custom':
        $link_title = $configuration['title'];
        break;

      default:
        $link_title = $entity->label();
    }

    if ($configuration['target']) {
      $options = $url->getOptions();
      $options['attributes']['target'] = '_blank';
      $url->setOptions($options);
    }

    if ($package = $configuration['package']) {
      $link_title = $this->icon($link_title)->setIcon($this->mimeManager->getMimeIcon($file->getMimeType(), $package));
    }
    return Link::fromTextAndUrl($link_title, $url)->toString();
  }

  /**
   * {@inheritdoc}
   */
  protected function viewPlainItem(EntityInterface $entity, FieldItemInterface $field_item, array $field) {
    $file = $field_item->entity;
    return $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri());
  }

}
