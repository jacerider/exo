<?php

namespace Drupal\exo_filter\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\views\ViewExecutable;

/**
 * Base class for eXo Filter plugins.
 */
abstract class ExoFilterBase extends PluginBase implements ExoFilterInterface {

  use StringTranslationTrait;

  /**
   * The views executable object.
   *
   * @var \Drupal\views\ViewExecutable
   */
  protected $view;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    $this->configuration = $configuration + $this->defaultConfiguration();
    $this->pluginId = $plugin_id;
    $this->pluginDefinition = $plugin_definition;
  }

  /**
   * {@inheritDoc}
   */
  public function defaultConfiguration() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function setView(ViewExecutable $view) {
    $this->view = $view;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    // Validation is optional.
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function exposedElementSettingsForm(&$element) {}

  /**
   * {@inheritdoc}
   */
  public static function isApplicable($handler = NULL, array $options = []) {
    return TRUE;
  }

  /**
   * Sets metadata on the form elements for easier processing.
   *
   * @param array $element
   *   The form element to apply the metadata to.
   *
   * @see ://www.drupal.org/project/drupal/issues/2511548
   */
  protected function addContext(array &$element) {
    $element['#context'] = [
      '#plugin_type' => 'bef',
      '#plugin_id' => $this->pluginId,
      '#view_id' => $this->view->id(),
      '#display_id' => $this->view->current_display,
    ];
  }

  /**
   * Returns exposed form action URL object.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Exposed views form state.
   *
   * @return \Drupal\Core\Url
   *   Url object.
   */
  protected function getExposedFormActionUrl(FormStateInterface $form_state) {
    /** @var \Drupal\views\ViewExecutable $view */
    $view = $form_state->get('view');
    $display = $form_state->get('display');

    if (isset($display['display_options']['path'])) {
      return Url::fromRoute(implode('.', [
        'view',
        $view->id(),
        $display['id'],
      ]));
    }

    $request = \Drupal::request();
    $url = Url::createFromRequest(clone $request);
    $url->setAbsolute();

    return $url;
  }

}
