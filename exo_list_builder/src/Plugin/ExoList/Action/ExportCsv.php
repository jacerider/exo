<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Action;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StreamWrapper\PrivateStream;
use Drupal\Core\Url;
use Drupal\exo_list_builder\EntityListInterface;
use Drupal\exo_list_builder\ExoListManagerInterface;
use Drupal\exo_list_builder\Plugin\ExoListActionBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a eXo list action for batch operations.
 *
 * @ExoListAction(
 *   id = "export_csv",
 *   label = @Translation("Export to CSV"),
 *   description = @Translation("Export to CSV file."),
 *   weight = 0,
 *   entity_type = {},
 *   bundle = {},
 * )
 */
class ExportCsv extends ExoListActionBase {

  /**
   * The temporary export directory.
   */
  const TEMPORARY_DIRECTORY = 'temporary://exo_entity_list/export';

  /**
   * The private export directory.
   */
  const PRIVATE_DIRECTORY = 'private://exo_entity_list/export';

  /**
   * The delimiter.
   */
  const DELIMITER = ',';

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The list element manager service.
   *
   * @var \Drupal\exo_list_builder\ExoListManagerInterface
   */
  protected $elementManager;

  /**
   * LogGeneratorBase constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\exo_list_builder\ExoListManagerInterface $element_manager
   *   The element manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, RendererInterface $renderer, ExoListManagerInterface $element_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager);
    $this->renderer = $renderer;
    $this->elementManager = $element_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('renderer'),
      $container->get('plugin.manager.exo_list_element')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function execute($entity_id, EntityListInterface $entity_list, $selected, array &$context) {
    $fields = $entity_list->getFields();
    if (!isset($context['results']['file_path'])) {
      $file_path = $this->prepareExportFile($entity_list, $context);
      $headers = [];
      foreach ($fields as $field_id => $field) {
        $headers[$field_id] = $field['display_label'] ?: $field['label'];
      }
      $handle = fopen($file_path, 'w');
      // Add BOM to fix UTF-8 in Excel.
      fwrite($handle, (chr(0xEF) . chr(0xBB) . chr(0xBF)));
      // Write headers now.
      fputcsv($handle, $headers, static::DELIMITER);
      fclose($handle);
    }

    $entity = $this->loadEntity($entity_list->getTargetEntityTypeId(), $entity_id);
    $handle = fopen($context['results']['file_path'], 'a');
    $row = [];
    foreach ($fields as $field_id => $field) {
      /** @var \Drupal\exo_list_builder\Plugin\ExoListElementInterface $instance */
      $instance = $this->elementManager->createInstance($field['view']['type'], $field['view']['settings']);
      $row[$field_id] = $instance->buildPlainView($entity, $field);
    }
    fputcsv($handle, $row, static::DELIMITER);
    fclose($handle);
  }

  /**
   * {@inheritdoc}
   */
  public function executeFinish(EntityListInterface $entity_list, array &$results) {
    // Hide default message.
    $results['entity_list_hide_message'] = TRUE;
    if (isset($results['file_uri']) && file_exists($results['file_uri'])) {
      $file_uri = $results['file_uri'];
      /** @var \Drupal\Core\Access\CsrfTokenGenerator $csrf_token */
      $csrf_token = \Drupal::service('csrf_token');
      $token = $csrf_token->get($file_uri);
      $query_options = [
        'query' => [
          'token' => $token,
          'file' => $file_uri,
        ],
      ];

      $download_url = Url::fromRoute('exo_list_builder.action.export.download', [
        'exo_entity_list' => $entity_list->id(),
      ], $query_options)->toString();
      \Drupal::messenger()->addStatus(t("Export successful. The download should automatically start shortly. If it doesn't, click <a data-auto-download href='@download_url'>Download</a>.", [
        '@download_url' => $download_url,
      ]));
    }
  }

  /**
   * Prepare the export file.
   *
   * @param \Drupal\exo_list_builder\EntityListInterface $entity_list
   *   The entity list.
   * @param array $context
   *   An array of the batch context.
   *
   * @return false|string
   *   The file path or false
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function prepareExportFile(EntityListInterface $entity_list, array &$context) {
    /** @var \Drupal\Core\File\FileSystemInterface $file_system */
    $file_system = \Drupal::service('file_system');
    $private_system_file = PrivateStream::basePath();
    if (!$private_system_file) {
      $directory = static::TEMPORARY_DIRECTORY;
    }
    else {
      $directory = static::PRIVATE_DIRECTORY;
    }
    $file_system->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY);
    $time = time();
    $filename = $entity_list->id() . '_' . $time . '.csv';
    $destination = $directory . '/' . $filename;
    /** @var \Drupal\file\FileRepositoryInterface $file_repository */
    $file_repository = \Drupal::service('file.repository');
    $file = $file_repository->writeData('', $destination, FileSystemInterface::EXISTS_ERROR);
    $file->setTemporary();
    $file->save();
    $file_path = $file_system->realpath($destination);
    $file_uri = $file->getFileUri();
    $context['results']['filename'] = $filename;
    $context['results']['file_path'] = $file_path;
    $context['results']['file_uri'] = $file_uri;

    return $file_path;
  }

}
