<?php

namespace Drupal\exo\Generators;

use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\exo\ExoThemeProviderPluginManagerInterface;
use DrupalCodeGenerator\Command\ModuleGenerator;
use DrupalCodeGenerator\Utils;
use Symfony\Component\Console\Question\Question;
use DrupalCodeGenerator\Asset\File;

/**
 *
 */
class ExoThemeGenerator extends ModuleGenerator {

  /**
   * {@inheritdoc}
   */
  protected string $name = 'exo:theme';

  /**
   * {@inheritdoc}
   */
  protected string $description = 'Generates an eXo theme.';

  /**
   * {@inheritdoc}
   */
  protected string $alias = 'exot';

  /**
   * {@inheritdoc}
   */
  protected string $templatePath = __DIR__ . '/ExoTheme';

  /**
   * Module extension list.
   *
   * @var \Drupal\Core\Extension\ModuleExtensionList
   */
  protected $moduleExtensionList;

  /**
   * The eXo theme provider manager.
   *
   * @var \Drupal\exo\ExoThemeProviderPluginManagerInterface
   */
  protected $exoThemeProviderManager;

  /**
   * Construct.
   */
  public function __construct(ModuleExtensionList $extension_list_module, ExoThemeProviderPluginManagerInterface $exo_theme_provider_manager) {
    parent::__construct($this->name);
    $this->moduleExtensionList = $extension_list_module;
    $this->exoThemeProviderManager = $exo_theme_provider_manager;
  }

  /**
   * An array of color options.
   */
  protected function getColorOptions() {
    return [
      'base' => '#373a3c',
      'offset' => '#f1f1f1',
      'primary' => '#2780e3',
      'secondary' => '#456072',
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function generate(&$vars): void {
    $this->collectDefault($vars);

    $vars['module'] = $vars['machine_name'];

    $default = Utils::camelize($vars['machine_name'] . 'Theme');
    $question = new Question('Theme class name', $default);
    $question->setValidator([static::class, 'validateRequired']);
    $vars['class'] = $this->io->askQuestion($question);

    $default = Utils::camelize($vars['machine_name']);
    $question = new Question('Theme label', $default);
    $question->setValidator([static::class, 'validateRequired']);
    $vars['label'] = $this->io->askQuestion($question);

    $default = $vars['machine_name'];
    $question = new Question('Plugin ID', $default);
    $question->setValidator([static::class, 'validateRequired']);
    $vars['plugin_id'] = Utils::human2machine($this->io->askQuestion($question));
    $vars['plugin_id_camel_upper'] = ucfirst(Utils::camelize(Utils::machine2human($vars['plugin_id'])));

    foreach ($this->getColorOptions() as $key => $value) {
      $vars['colors'][$key] = $this->ask(ucfirst($key) . ' Color ', $value);
    }

    $this->addFile('src/ExoTheme/{plugin_id_camel_upper}/{class}.php', 'exo-theme.php.twig');
    $this->addFile('src/ExoTheme/{plugin_id_camel_upper}/scss/_exo-theme.scss', '_exo-theme.scss.twig');

    $this->getHelper('renderer')->prependPath(__DIR__ . '/GulpScss');
    $this->addFile('.gitignore', '.gitignore.twig');
    $this->addFile('gulpfile.js', 'gulpfile.js.twig');
    $this->addFile('package.json', 'package.json.twig');

    $module_path = $this->moduleExtensionList->getPath($vars['machine_name']);
    $plugin_path = $module_path . '/src/ExoTheme/' . $vars['plugin_id_camel_upper'];
    $plugin_asset_path = $plugin_path . '/scss';
    foreach ($this->exoThemeProviderManager->getAllDefinitions() as $provider_plugin_id => $definition) {
      $this->getHelper('renderer')->prependPath($definition['providerPath'] . '/src/ExoThemeProvider');
      $vars['theme_relative_path'] = $this->getRelativePath($plugin_asset_path, $definition['providerPath']);
      $asset = new File('src/ExoTheme/{plugin_id_camel_upper}/scss/' . str_replace('_', '-', $definition['id']) . '.scss');
      $asset->template($definition['template']);
      $asset->vars($vars);
      $this->assets[] = $asset;
      // $this->addFile('src/ExoTheme/{plugin_id_camel_upper}/scss/' . str_replace('_', '-', $definition['id']) . '.scss', $definition['template']);
    }
  }

  /**
   * Find the relative file system path between two file system paths.
   *
   * @param string $from
   *   Path to start from.
   * @param string $to
   *   Path we want to end up in.
   * @param string $ps
   *   The directory separator.
   *
   * @return string
   *   Path leading from $from to $to
   */
  protected function getRelativePath($from, $to, $ps = DIRECTORY_SEPARATOR) {
    $arFrom = explode($ps, rtrim($from, $ps));
    $arTo = explode($ps, rtrim($to, $ps));
    while (count($arFrom) && count($arTo) && ($arFrom[0] == $arTo[0])) {
      array_shift($arFrom);
      array_shift($arTo);
    }
    $path = str_pad("", count($arFrom) * 3, '..' . $ps) . implode($ps, $arTo);
    if (substr($path, -1) !== '/') {
      $path .= '/';
    }
    return $path;
  }

}
