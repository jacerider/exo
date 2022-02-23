<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\exo\ExoSettingsPluginInterface;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldComputedBase;
use Drupal\exo_menu\ExoMenuGeneratorInterface;

/**
 * A 'menu' adapter for exo components.
 *
 * @ExoComponentField(
 *   id = "menu",
 *   label = @Translation("Menu"),
 *   provider = "exo_menu",
 * )
 */
class Menu extends ExoComponentFieldComputedBase implements ContainerFactoryPluginInterface {

  /**
   * The eXo Menu options service.
   *
   * @var \Drupal\exo\ExoSettingsPluginSelectInstanceInterface
   */
  protected $exoSettings;

  /**
   * The eXo menu generator.
   *
   * @var \Drupal\exo_menu\ExoMenuGeneratorInterface
   */
  protected $exoMenuGenerator;

  /**
   * Creates a PageTitle instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\exo\ExoSettingsPluginInterface $exo_settings
   *   The eXo options service.
   * @param \Drupal\exo_menu\ExoMenuGeneratorInterface $exo_menu_generator
   *   The eXo menu generator.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ExoSettingsPluginInterface $exo_settings, ExoMenuGeneratorInterface $exo_menu_generator) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    // $this->exoSettings = $exo_settings->createPluginSelectInstance($this->configuration['menu']);
    $this->exoSettings = $exo_settings;
    $this->exoMenuGenerator = $exo_menu_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('exo_menu.settings'),
      $container->get('exo_menu.generator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processDefinition() {
    parent::processDefinition();
    $field = $this->getFieldDefinition();
    if (!$field->hasAdditionalValue('menu_ids')) {
      throw new PluginException(sprintf('eXo Component Field plugin (%s) requires [menu_ids] be set.', $field->getType()));
    }
    $menu_ids = $field->getAdditionalValue('menu_ids');
    if (!is_array($menu_ids)) {
      $field->setAdditionalValue('menu_ids', [$menu_ids]);
    }
    $field->setAdditionalValueIfEmpty('menu_style', 'tree');
  }

  /**
   * {@inheritdoc}
   */
  public function propertyInfo() {
    $properties = parent::propertyInfo();
    $properties['render'] = $this->t('The menu renderable.');
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function viewValue(ContentEntityInterface $entity, array $contexts) {
    $field = $this->getFieldDefinition();
    $value = parent::viewValue($entity, $contexts);
    $value['render'] = $this->exoMenuGenerator->generate(
      $field->id(),
      $field->getAdditionalValue('menu_style'),
      $field->getAdditionalValue('menu_ids'),
      $field->getAdditionalValue('menu_settings') ?? []
    )->toRenderable();
    return $value;
  }

}
