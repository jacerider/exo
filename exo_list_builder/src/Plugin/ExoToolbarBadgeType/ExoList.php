<?php

namespace Drupal\exo_list_builder\Plugin\ExoToolbarBadgeType;

use Drupal\Core\Cache\Cache;
use Drupal\exo_toolbar\Plugin\ExoToolbarBadgeTypeBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_toolbar\ExoToolbarElementInterface;
use Drupal\exo_toolbar\Plugin\ExoToolbarItemPluginInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'exo_list' eXo toolbar badge type.
 *
 * @ExoToolbarBadgeType(
 *   id = "exo_list",
 *   label = @Translation("eXo List"),
 * )
 */
class ExoList extends ExoToolbarBadgeTypeBase implements ContainerFactoryPluginInterface {

  /**
   * The entity storage class.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $exoListStorage;

  /**
   * The eXo list.
   *
   * @var \Drupal\exo_list_builder\EntityListInterface
   *
   * @var [type]
   */
  protected $list;

  /**
   * Adds a LocalTasksEscort instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\EntityStorageInterface $exo_entity_list
   *   The entity storage class.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityStorageInterface $exo_entity_list) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->exoListStorage = $exo_entity_list;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')->getStorage('exo_entity_list')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'exo_list_id' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function badgeTypeForm(array $form, FormStateInterface $form_state) {
    $form = parent::badgeTypeForm($form, $form_state);
    $form['exo_list_id'] = [
      '#type' => 'select',
      '#title' => $this->t('List'),
      '#options' => $this->getOptions(),
      '#default_value' => $this->configuration['exo_list_id'],
      '#required' => TRUE,
      '#empty_option' => $this->t('- Select -'),
      '#multiple' => FALSE,
    ];
    return $form;
  }

  /**
   * Helper function to get all display ids.
   *
   * @return \Drupal\exo_list_builder\EntityListInterface
   *   The list.
   */
  protected function getList() {
    if (!isset($this->list)) {
      $this->list = $this->exoListStorage->load($this->configuration['exo_list_id']);
    }
    return $this->list;
  }

  /**
   * Helper function to get all display ids.
   */
  protected function getOptions() {
    $options = [];
    foreach ($this->exoListStorage->loadMultiple() as $list) {
      /** @var \Drupal\exo_list_builder\EntityListInterface $list */
      if ($list->status()) {
        $options[$list->get('id')] = $list->get('label');
      }
    }
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function elementPrepare(ExoToolbarElementInterface $element, $delta, ExoToolbarItemPluginInterface $item) {
    parent::elementPrepare($element, $delta, $item);
    $cid = 'exo_list:exo_toolbar_badge:' . $item->getItem()->id();
    $total = 0;
    if ($cache = \Drupal::cache()->get($cid)) {
      $total = $cache->data;
    }
    else {
      if ($list = $this->getList()) {
        $handler = $list->getHandler();
        $total = $handler->getRawTotal(TRUE);
      }
      \Drupal::cache()->set($cid, $total, Cache::PERMANENT, $item->getItem()->getCacheTags());
    }
    $element->setBadge($total);
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    $dependencies = parent::calculateDependencies();
    if ($list = $this->exoListStorage->load($this->configuration['exo_list_id'])) {
      $dependencies['config'][] = $list->getConfigDependencyName();
    }
    return $dependencies;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $list = $this->getList();
    return $list ? $list->getCacheTags() : [];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    $list = $this->getList();
    return $list ? $list->getCacheContexts() : [];
  }

}
