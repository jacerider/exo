<?php

namespace Drupal\exo\Drush\Generators;

use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\exo\ExoThemeProviderPluginManagerInterface;
use DrupalCodeGenerator\Application;
use DrupalCodeGenerator\Utils;
use DrupalCodeGenerator\Attribute\Generator;
use DrupalCodeGenerator\GeneratorType;
use DrupalCodeGenerator\Command\BaseGenerator;
use DrupalCodeGenerator\Asset\AssetCollection;
use DrupalCodeGenerator\Helper\Renderer\TwigRenderer;
use DrupalCodeGenerator\Twig\TwigEnvironment;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Loader\FilesystemLoader as TemplateLoader;

#[Generator(
  name: 'exo:theme',
  description: 'Generates eXo theme module',
  templatePath: __DIR__ . '/ExoTheme',
  type: GeneratorType::MODULE_COMPONENT,
)]
/**
 * Exo theme generator.
 */
final class ExoThemeGenerator extends BaseGenerator {

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
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new self($container->get('extension.list.module'), $container->get('plugin.manager.exo_theme_provider'));
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
  protected function generate(array &$vars, AssetCollection $assets): void {
    $ir = $this->createInterviewer($vars);
    $vars['machine_name'] = $ir->askMachineName();
    $vars['class'] = $ir->askClass(default: '{machine_name|camelize}Theme');
    $vars['label'] = $ir->ask('Theme label', '{machine_name|m2h} Theme');
    $vars['plugin_id'] = $ir->ask('Plugin ID', '{machine_name}');
    $vars['plugin_id_camel_upper'] = ucfirst(Utils::camelize(Utils::machine2human($vars['plugin_id'])));

    foreach ($this->getColorOptions() as $key => $value) {
      $vars['colors'][$key] = $ir->ask(ucfirst($key) . ' Color ', $value);
    }

    $assets->addFile('src/ExoTheme/{plugin_id_camel_upper}/{class}.php', 'exo-theme.php.twig');
    $assets->addFile('src/ExoTheme/{plugin_id_camel_upper}/scss/_exo-theme.scss', '_exo-theme.scss.twig');

    $this->getHelper('renderer')->registerTemplatePath(__DIR__ . '/GulpScss');
    $assets->addFile('.gitignore', '.gitignore.twig');
    $assets->addFile('gulpfile.js', 'gulpfile.js.twig');
    $assets->addFile('package.json', 'package.json.twig');

    // Prepare includes.
    $vars['includes'] = $this->getThemeIncludes();

    $module_path = $this->moduleExtensionList->getPath($vars['machine_name']);
    $plugin_path = $module_path . '/src/ExoTheme/' . $vars['plugin_id_camel_upper'];
    $plugin_asset_path = $plugin_path . '/scss';
    foreach ($this->exoThemeProviderManager->getAllDefinitions() as $provider_plugin_id => $definition) {
      $collection = new AssetCollection();
      $template_loader = new TemplateLoader();
      $template_loader->addPath(Application::TEMPLATE_PATH . '/_lib', 'lib');
      $renderer = new TwigRenderer(new TwigEnvironment($template_loader));
      $renderer->setLogger($this->logger);
      $renderer->registerTemplatePath($definition['providerPath'] . '/src/ExoThemeProvider');
      $vars['theme_relative_path'] = $this->getRelativePath($plugin_asset_path, $definition['providerPath']);
      $collection->addFile('src/ExoTheme/{plugin_id_camel_upper}/scss/' . str_replace('_', '-', $definition['id']) . '.scss', $definition['template'])->vars($vars);
      foreach ($collection->getFiles() as $file) {
        /** @var \DrupalCodeGenerator\Asset\File $file */
        $file->render($renderer);
      }
      $destination = $this->getDestination($vars);
      $dumped_assets = $this->dump($collection, $destination);
      $this->printSummary($dumped_assets, $destination . '/');
    }
  }

  /**
   * Get theme includes for use in theme SCSS file.
   *
   * @return string
   *   A string of theme includes.
   */
  protected function getThemeIncludes() {
    // Prepare includes.
    $includes = ['exo-theme'];
    array_walk($includes, function (&$include) {
      $include = "@import '$include';";
    });
    return implode("\n", $includes);
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
