<?php

namespace Drupal\exo_icon\Command;

use Drupal\Core\File\FileSystemInterface;
use Drupal\exo_icon\ExoIconRepositoryInterface;
use Drush\Commands\DrushCommands;

/**
 * Defines Drush commands for the Search API.
 */
class ExoIconDrushCommands extends DrushCommands {

  /**
   * The eXo theme manager.
   *
   * @var \Drupal\exo\ExoIconThemePluginManagerInterface
   */
  protected $exoIconManager;

  /**
   * The file system manager.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Constructs a ExoIconDrushCommands object.
   *
   * @param \Drupal\exo_icon\ExoIconRepositoryInterface $exo_icon_manager
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
  public function __construct(ExoIconRepositoryInterface $exo_icon_manager, FileSystemInterface $file_system) {
    $this->exoIconManager = $exo_icon_manager;
    $this->fileSystem = $file_system;
  }

  /**
   * Generate global icon.scss.
   *
   * @param string $path
   *   The relative path for the generated scss file.
   *
   * @command exo:exo-icon
   *
   * @usage drush exo:icon
   *   Run the exo theme generator.
   *
   * @aliases exo-icon
   *
   * @throws \Exception
   *   If no index or no server were passed or passed values are invalid.
   */
  public function exoIcons($path) {

    // If no $name provided, abort.
    if (!$path) {
      $this->logger()->info(dt('Location path missing. See help using drush exo-icon --help.'));
      return;
    }

    $path = drush_get_context('DRUSH_DRUPAL_ROOT') . '/' . $path;
    if (!file_exists($path)) {
      $this->logger()->info(dt('Location directory (' . $path . ') not found. See help using drush exo-icon --help.'));
      return;
    }

    $fullpath = $path . '/_icons.scss';
    $exo_icon_repository = $this->exoIconManager;

    $content = [];
    $content[] = '/**';
    $content[] = '* eXo icon mixins and variables.';
    $content[] = '*';
    $content[] = '* DO NOT MAKE MANUAL CHANGES TO THIS FILE';
    $content[] = '* Generate via `drush exo_icon ' . $path . '`.';
    $content[] = '*/' . "\n";
    $content[] = '@mixin icon($package: regular, $icon: rebel, $position: before) {';
    $content[] = '  $package: icon-#{$package};';
    $content[] = '  @if $position == both {';
    $content[] = '    $position: \'before, &:after\';';
    $content[] = '  }' . "\n";
    $content[] = '  &:#{$position} {';
    $content[] = '    font-family: \'#{$package}\' !important; /* stylelint-disable-line declaration-no-important */';
    $content[] = '    display: inline-block;';
    $content[] = '    speak: none;';
    $content[] = '    font-style: normal;';
    $content[] = '    font-weight: normal;';
    $content[] = '    font-variant: normal;';
    $content[] = '    text-transform: none;';
    $content[] = '    line-height: 1;';
    $content[] = '    -webkit-font-smoothing: antialiased; // sass-lint:disable-line no-vendor-prefixes';
    $content[] = '    -moz-osx-font-smoothing: grayscale; // sass-lint:disable-line no-vendor-prefixes';
    $content[] = '    content: "#{map-get($icons, #{$package}-#{$icon})}"; /* stylelint-disable-line string-quotes */';
    $content[] = '    @content;';
    $content[] = '  }';
    $content[] = '}' . "\n";

    $content[] = '$icons: (';
    foreach ($exo_icon_repository->getPackagesByStatus() as $package) {
      /** @var \Drupal\exo_icon\Entity\ExoIconPackage $package */
      foreach ($package->getInstances() as $icon) {
        $content[] = '  ' . $icon->getSelector() . ': \'' . $icon->getHex() . '\',';
      }
    }
    $content[] = '); /* stylelint-disable-line max-empty-lines */';

    $content[] = "\n";

    file_put_contents($fullpath, implode("\n", $content));

    // Notify user.
    $message = 'Successfully created the eXo SCSS file in: !path';

    $message = dt($message . '.', [
      '!path' => $path,
    ]);
    $this->logger()->info($message);
  }

}
