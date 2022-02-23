<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\exo_alchemist\Command\ExoComponentCommand;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldComputedBase;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldDisplayFormTrait;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldPreviewEntityTrait;
use Drupal\views\Views;

/**
 * A 'view' adapter for exo components.
 *
 * @ExoComponentField(
 *   id = "view",
 *   label = @Translation("View"),
 * )
 */
class View extends ExoComponentFieldComputedBase {

  use ExoComponentFieldDisplayFormTrait;
  use ExoComponentFieldPreviewEntityTrait;

  /**
   * Already loaded and processed views.
   *
   * @var array
   */
  protected static $value = [];

  /**
   * {@inheritdoc}
   */
  public function processDefinition() {
    parent::processDefinition();
    $field = $this->getFieldDefinition();
    if (!$field->hasAdditionalValue('view_id')) {
      throw new PluginException(sprintf('eXo Component Field plugin (%s) requires [view_id] be set.', $field->getType()));
    }
    if (!$field->hasAdditionalValue('view_display')) {
      $field->setAdditionalValue('view_display', 'default');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function propertyInfo() {
    $properties = [
      'render' => $this->t('The view renderable.'),
      'view' => $this->t('The view executable.'),
      'view_display' => $this->t('The view display id.'),
    ];
    if ($this->getFieldDefinition()->getAdditionalValue('view_filter')) {
      $properties['view_filter'] = $this->t('The view filters renderable.');
    }
    if ($this->getFieldDefinition()->getAdditionalValue('view_count')) {
      $properties['view_count'] = $this->t('The total number of results.');
    }
    return $properties;
  }

  /**
   * Array to string.
   *
   * @param array $keys
   *   The array.
   *
   * @return string
   *   The string.
   */
  protected function arrayToString(array $keys) {
    foreach ($keys as $delta => $key) {
      if (is_array($key)) {
        $keys[$delta] = $this->arrayToString($key);
      }
      elseif (is_bool($key)) {
        $keys[$delta] = $key ? '1' : '0';
      }
    }
    return implode('.', $keys);
  }

  /**
   * {@inheritdoc}
   */
  public function viewValue(ContentEntityInterface $entity, array $contexts) {
    $value = [];
    $field = $this->getFieldDefinition();
    $cache_key = substr(hash('sha256', $this->arrayToString([
      $field->getName(),
      $this->isDefaultStorage($contexts),
      $this->isPreview($contexts),
    ] + $field->getAdditional())), 0, 20);
    if (isset(static::$value[$cache_key])) {
      return static::$value[$cache_key];
    }
    // Pass information to views. This allows components to alter the view.
    // @see exo_alchemist_views_pre_view().
    $view = Views::getView($field->getAdditionalValue('view_id'));
    $key = 'exo-c-' . str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(implode(',', [
      $entity->id(),
      $field->getName(),
      $this->isDefaultStorage($contexts),
      $this->isPreview($contexts),
      $field->getAdditionalValue('view_id'),
      $field->getAdditionalValue('view_display'),
    ])));
    $view->exoAlchemist = [
      'key' => $key,
      'entity_id' => $entity->id(),
    ];
    if ($view) {
      $view->setDisplay($field->getAdditionalValue('view_display'));
      $args = [];
      // Allow args to be provided when previewing.
      if (($preview_args = $field->getAdditionalValue('view_preview_args')) && ($this->isPreview($contexts) || $this->isDefaultStorage($contexts))) {
        $args = $this->buildPreviewArgs($preview_args);
      }
      else {
        $args = $field->getAdditionalValue('view_args') ?? $args;
      }
      if ($args) {
        $view->setArguments($args);
      }
      $render = $view->buildRenderable(NULL, $args, FALSE);
      if ($field->getAdditionalValue('view_count')) {
        $view->build($field->getAdditionalValue('view_display'));
        /** @var \Drupal\Core\Database\Query\Select $query */
        $query = $view->query->query();
        $value['count'] = (int) $query->countQuery()->execute()->fetchField();
      }
      if ($field->getAdditionalValue('view_exposed_form_options')) {
        $options = $view->getDisplay()->getOption('exposed_form');
        $options['options'] = $field->getAdditionalValue('view_exposed_form_options') + $options['options'];
        $view->getDisplay()->options['exposed_form'] = $options;
      }
      if ($field->getAdditionalValue('view_pager_options')) {
        $options = $view->getDisplay()->getOption('pager');
        $options['options'] = $field->getAdditionalValue('view_pager_options') + $options['options'];
        $view->getDisplay()->options['pager'] = $options;
      }
      $value['view'] = $view;
      $value['view_display'] = $field->getAdditionalValue('view_display');
      if ($field->getAdditionalValue('view_filter')) {
        $view->initHandlers();
        /** @var \Drupal\views\Plugin\views\exposed_form\ExposedFormPluginInterface $exposed_form */
        $exposed_form = $view->display_handler->getPlugin('exposed_form');
        $value['view_filter'] = $exposed_form->renderExposedForm(TRUE);
        $view->getDisplay()->options['exposed_block'] = TRUE;
      }
      if ($this->isLayoutBuilder($contexts)) {
        // Views can contain forms.
        $render = $this->getFormAsPlaceholder($render);
      }
      $render['#cache']['keys'][] = $cache_key;
      if (!empty($args)) {
        $render['#cache']['keys'][] = implode('_', $args);
      }
      $value['render'] = $render;
      $this->addCacheableDependency($contexts, $render);
    }
    static::$value[$cache_key] = $value;
    return $value;
  }

  /**
   * Build preview args.
   *
   * @param array $preview_args
   *   The preview args provided by the field.
   */
  protected function buildPreviewArgs(array $preview_args) {
    $args = [];
    foreach ($preview_args as $arg) {
      if (is_array($arg)) {
        if (isset($arg['type'])) {
          switch ($arg['type']) {
            case 'entity':
              if ($preview_entity = $this->getPreviewEntity($arg['entity_type'], $arg['bundle'])) {
                $args[] = $preview_entity->id();
              }
              break;
          }
        }
      }
      else {
        $args[] = $arg;
      }
    }
    return $args;
  }

  /**
   * {@inheritdoc}
   */
  public static function buildCommand(ExoComponentCommand $command, array &$data) {
    $data['view_id'] = $command->getIo()->ask(
      t('View ID'),
      NULL
    );
  }

}
