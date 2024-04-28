<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Action;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\exo_list_builder\EntityListInterface;
use Drupal\exo_list_builder\ExoListBuilderInterface;
use Drupal\exo_list_builder\ExoListManagerInterface;
use Drupal\exo_list_builder\Plugin\ExoListActionBase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a eXo list action for batch operations.
 *
 * Requres the PhpOffice\PhpSpreadsheet library.
 * Run: composer require phpoffice/phpspreadsheet.
 *
 * @ExoListAction(
 *   id = "export_spreadsheet",
 *   label = @Translation("Export to Spreadsheet"),
 *   description = @Translation("Export to spreadsheet file."),
 *   weight = 0,
 *   entity_type = {},
 *   bundle = {},
 *   queue = true,
 * )
 */
class ExportSpreadsheet extends ExoListActionBase {

  /**
   * The private export directory.
   *
   * @var string
   */
  const SPREADSHEET_DIRECTORY = 'private://exo-entity-list/export';

  /**
   * Store spreadsheet permanently.
   *
   * @var bool
   */
  const SPREADSHEET_PRESERVE = FALSE;

  /**
   * Store spreadsheet as a managed file.
   *
   * @var bool
   */
  const SPREADSHEET_MANAGED = TRUE;

  /**
   * The delimiter.
   *
   * @var string
   */
  const SPREADSHEET_TYPE = 'Xlsx';

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
  public function executeStart(EntityListInterface $entity_list, array &$context) {
    parent::executeStart($entity_list, $context);
    $file_path = $this->prepareSpreadsheetFile($entity_list, $context);
    $headers = $this->getSpreadsheetHeader($entity_list, $context);
    if ($headers) {
      $spreadsheet = IOFactory::load($file_path);
      $sheet = $spreadsheet->getActiveSheet();
      $sheet->fromArray($headers, NULL, 'A1');
      $writer = IOFactory::createWriter($spreadsheet, self::SPREADSHEET_TYPE);
      $writer->save($file_path);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function execute($entity_id, EntityListInterface $entity_list, $selected, array &$context) {
    $spreadsheet = IOFactory::load($context['results']['spreadsheet_file_path']);

    $sheet = $spreadsheet->getActiveSheet();
    $row = $this->getSpreadsheetRow($entity_id, $entity_list, $selected, $context);
    $delta = count($context['results']['entity_ids_complete']);
    $sheet->fromArray($row, NULL, 'A' . $delta + 2);

    $writer = IOFactory::createWriter($spreadsheet, self::SPREADSHEET_TYPE);
    $writer->save($context['results']['spreadsheet_file_path']);
  }

  /**
   * {@inheritdoc}
   */
  public function executeFinish(EntityListInterface $entity_list, array &$results) {
    $spreadsheet = IOFactory::load($results['spreadsheet_file_path']);
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setAutoFilter(
      $sheet->calculateWorksheetDimension()
    );
    $writer = IOFactory::createWriter($spreadsheet, self::SPREADSHEET_TYPE);
    $writer->save($results['spreadsheet_file_path']);

    parent::executeFinish($entity_list, $results);

    // Hide default message.
    $results['entity_list_hide_message'] = TRUE;
    if (PHP_SAPI !== 'cli' && isset($results['spreadsheet_file_uri']) && file_exists($results['spreadsheet_file_uri'])) {
      $file_uri = $results['spreadsheet_file_uri'];
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
   * {@inheritdoc}
   */
  protected function notifyEmailFinish(EntityListInterface $entity_list, array $results, $email, $subject = NULL, $message = NULL, $link_text = NULL, $link_url = NULL, $attachments = []) {
    if ($this->supportsJobQueue()) {
      $message = $this->t('A submission spreadsheet has been prepared.', [
        '%label' => $entity_list->label(),
        '%action' => $this->label(),
        '@url' => $entity_list->toUrl()->setAbsolute()->toString(),
      ]);
      $link_text = $link_text ?: $this->t('Download Spreadsheet');
      $link_url = \Drupal::service('file_url_generator')->generateAbsoluteString($results['spreadsheet_file_uri']);

      $attachments[] = [
        'filepath' => $results['spreadsheet_file_uri'],
        'filename' => $results['spreadsheet_filename'],
        'filemime' => 'application/vnd.ms-excel',
      ];
    }
    return parent::notifyEmailFinish($entity_list, $results, $email, $subject, $message, $link_text, $link_url, $attachments);
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
  public function prepareSpreadsheetFile(EntityListInterface $entity_list, array &$context) {
    /** @var \Drupal\Core\File\FileSystemInterface $file_system */
    $file_system = \Drupal::service('file_system');
    $directory = $this->getSpreadsheetDirectory($entity_list, $context);
    $file_system->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);
    $filename = $this->getSpreadsheetFilename($entity_list, $context);
    $file_uri = $directory . '/' . $filename;
    $file_path = $file_system->realpath($file_uri);
    $spreadsheet = new Spreadsheet();
    $writer = IOFactory::createWriter($spreadsheet, self::SPREADSHEET_TYPE);

    if (static::SPREADSHEET_MANAGED) {
      /** @var \Drupal\file\FileRepositoryInterface $file_repository */
      $file_repository = \Drupal::service('file.repository');
      if (file_exists($file_uri)) {
        $file_system->delete($file_uri);
      }
      $file = $file_repository->writeData('', $file_uri, FileSystemInterface::EXISTS_ERROR);
      if (static::SPREADSHEET_PRESERVE) {
        $file->setPermanent();
      }
      else {
        $file->setTemporary();
      }
      $file->save();
      \Drupal::service('file.usage')->add($file, 'file', $entity_list->getEntityTypeId(), $entity_list->id());
      // URI may have changed.
      $file_uri = $file->getFileUri();
    }
    else {
      if (file_exists($file_uri)) {
        $file_system->delete($file_uri);
      }
      $file_system->saveData('', $file_uri);
    }
    $writer->save($file_uri);

    $context['results']['spreadsheet_filename'] = $filename;
    $context['results']['spreadsheet_file_path'] = $file_path;
    $context['results']['spreadsheet_file_uri'] = $file_uri;
    return $file_path;
  }

  /**
   * Get spreadsheet file directory.
   *
   * @param \Drupal\exo_list_builder\EntityListInterface $entity_list
   *   The entity list.
   * @param array $context
   *   An array of the batch context.
   *
   * @return string
   *   The file directory.
   */
  protected function getSpreadsheetDirectory(EntityListInterface $entity_list, array &$context) {
    return static::SPREADSHEET_DIRECTORY;
  }

  /**
   * Get spreadsheet file filename.
   *
   * @param \Drupal\exo_list_builder\EntityListInterface $entity_list
   *   The entity list.
   * @param array $context
   *   An array of the batch context.
   *
   * @return string
   *   The file name.
   */
  protected function getSpreadsheetFilename(EntityListInterface $entity_list, array &$context) {
    $filename = md5($entity_list->id() . $this->getPluginId());
    if (static::SPREADSHEET_PRESERVE) {
      $filename .= '-' . \Drupal::time()->getRequestTime();
    }
    return $filename . '.xlsx';
  }

  /**
   * Get spreadsheet file headers.
   *
   * @param \Drupal\exo_list_builder\EntityListInterface $entity_list
   *   The entity list.
   * @param array $context
   *   The batch context.
   *
   * @return array
   *   The spreadsheet file headers.
   */
  protected function getSpreadsheetHeader(EntityListInterface $entity_list, array &$context) {
    $headers = [];
    foreach ($entity_list->getFields() as $field_id => $field) {
      $headers[$field_id] = $field['display_label'] ?: $field['label'];
    }
    return $headers;
  }

  /**
   * Get spreadsheet file row.
   *
   * @param string $entity_id
   *   The entity id.
   * @param \Drupal\exo_list_builder\EntityListInterface $entity_list
   *   The entity list.
   * @param bool $selected
   *   Will be true if entity was selected.
   * @param array $context
   *   The batch context.
   */
  protected function getSpreadsheetRow($entity_id, EntityListInterface $entity_list, $selected, array &$context) {
    $row = [];
    $entity = $this->loadEntity($entity_list->getTargetEntityTypeId(), $entity_id);
    foreach ($entity_list->getFields() as $field_id => $field) {
      $field_entity = $entity_list->getHandler()->getFieldEntity($entity, $field);
      if (!$field_entity) {
        $row[$field_id] = NULL;
        continue;
      }
      /** @var \Drupal\exo_list_builder\Plugin\ExoListElementInterface $instance */
      $instance = $this->elementManager->createInstance($field['view']['type'], $field['view']['settings']);
      $row[$field_id] = $instance->buildPlainView($field_entity, $field);
      if ($row[$field_id] === $instance->getConfiguration()['empty']) {
        $row[$field_id] = NULL;
      }
    }
    return $row;
  }

  /**
   * {@inheritdoc}
   */
  public function overview(array $context) {
    if (!empty($context['job_finish'])) {
      return [
        '#type' => 'link',
        '#title' => $this->t('Download spreadsheet'),
        '#url' => \Drupal::service('file_url_generator')->generate($context['results']['spreadsheet_file_uri']),
      ];
    }
    return parent::overview($context);
  }

  /**
   * {@inheritdoc}
   */
  public function applies(ExoListBuilderInterface $exo_list) {
    return class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet');
  }

}
