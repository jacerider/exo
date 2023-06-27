<?php

namespace Drupal\exo\Drush\Commands;

use Drupal\Core\File\FileSystemInterface;
use Drupal\exo\ExoThemePluginManagerInterface;
use Drush\Commands\DrushCommands;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines Drush commands for the Search API.
 */
class ExoDrushCommands extends DrushCommands {

  /**
   * The eXo theme manager.
   *
   * @var \Drupal\exo\ExoThemePluginManagerInterface
   */
  protected $exoThemeManager;

  /**
   * The file system manager.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Constructs a ExoDrushCommands object.
   *
   * @param \Drupal\exo\ExoThemePluginManagerInterface $exo_theme_manager
   *   The eXo theme manager.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system manager.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   *   Thrown if the "search_api_index" or "search_api_server" entity types'
   *   storage handlers couldn't be loaded.
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *   Thrown if the "search_api_index" or "search_api_server" entity types are
   *   unknown.
   */
  public function __construct(ExoThemePluginManagerInterface $exo_theme_manager, FileSystemInterface $file_system) {
    $this->exoThemeManager = $exo_theme_manager;
    $this->fileSystem = $file_system;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new self($container->get('plugin.manager.exo_theme'), $container->get('file_system'));
  }

  /**
   * Generate global exo-theme.scss.
   *
   * @command exo:exo-scss
   *
   * @usage drush exo:scss
   *   Run the exo theme generator.
   *
   * @aliases exo-scss
   *
   * @throws \Exception
   *   If no index or no server were passed or passed values are invalid.
   */
  public function exoScss() {
    $root_path = $_ENV['DDEV_EXTERNAL_ROOT'] ?? DRUPAL_ROOT;
    $dirname = 'public://exo';
    $this->fileSystem->prepareDirectory($dirname, FileSystemInterface::CREATE_DIRECTORY);

    /** @var \\Drupal\file\FileRepositoryInterface $file_service */
    $file_service = \Drupal::service('file.repository');
    $module_path = \Drupal::service('extension.list.module')->getPath('exo');

    // Generate exo-common.scss.
    $destination = $dirname . '/exo-common.scss';
    $internal_path = DRUPAL_ROOT . '/' . $module_path;
    $external_path = $root_path . '/' . $module_path;
    $data = file_get_contents($internal_path . '/src/scss/_common.scss');
    $data = str_replace("@import '", "@import '" . $external_path . '/src/scss/', $data);
    $file_service->writeData($data, $destination, FileSystemInterface::EXISTS_REPLACE);

    // Generate exo-theme.scss.
    $destination = $dirname . '/exo-theme.scss';
    $theme = \Drupal::service('plugin.manager.exo_theme')->getCurrentTheme();
    $external_path = $root_path . '/' . $theme->getScssPath() . '/exo-theme';
    $data = "@import '$external_path';";
    $file_service->writeData($data, $destination, FileSystemInterface::EXISTS_REPLACE);

    $this->logger()->info(dt('eXo utilities generated at @destination', ['@destination' => $dirname]));
  }

}
