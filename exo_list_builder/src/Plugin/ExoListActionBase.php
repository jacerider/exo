<?php

namespace Drupal\exo_list_builder\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\exo_list_builder\EntityListInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\exo_list_builder\ExoListBuilderInterface;
use Drupal\user\Entity\User;

/**
 * Base class for eXo list actions.
 */
abstract class ExoListActionBase extends PluginBase implements ExoListActionInterface, ContainerFactoryPluginInterface {

  use StringTranslationTrait;
  use DependencySerializationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

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
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->setConfiguration($configuration);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    return $this->getPluginDefinition()['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function asJobQueue() {
    return !empty($this->getPluginDefinition()['queue']);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration + $this->defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, EntityListInterface $entity_list, array $action) {
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array $form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityIds(array $selected_ids, ExoListBuilderInterface $exo_list_builder) {
    if (empty($selected_ids)) {
      // If none are selected, process all.
      $selected_ids = $exo_list_builder->getQuery()->execute();
    }
    return $selected_ids;
  }

  /**
   * {@inheritdoc}
   */
  public function executeStart(EntityListInterface $entity_list, array &$context) {
    if ($this->asJobQueue()) {
      if ($email = $this->getNotifyEmail()) {
        \Drupal::messenger()->addMessage($this->t('Started action "@action". This process will continue in the background. When finished, a notification email will be sent to %email.', [
          '@action' => $this->label(),
          '%email' => $email,
        ]));
      }
      else {
        \Drupal::messenger()->addMessage($this->t('Started action "@action". This process will continue in the background and you can view the status of the action in the "active actions overview" section of this page.', [
          '@action' => $this->label(),
        ]));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function execute($entity_id, EntityListInterface $entity_list, $selected, array &$context) {
  }

  /**
   * {@inheritdoc}
   */
  public function executeFinish(EntityListInterface $entity_list, array &$results) {
    if ($this->asJobQueue() && ($email = $this->getNotifyEmail())) {
      $this->notifyEmailFinish($entity_list, $results, $email);
    }
  }

  /**
   * An optional email no notify when the job queue has finished.
   *
   * @return string
   *   The email to notify.
   */
  protected function getNotifyEmail() {
    return NULL;
  }

  /**
   * Notify via email.
   *
   * @param \Drupal\exo_list_builder\EntityListInterface $entity_list
   *   The entity list.
   * @param array $results
   *   The batch results.
   * @param string $email
   *   A comma separated list of email addresses.
   * @param string $subject
   *   The email subject.
   * @param mixed $message
   *   The email message.
   * @param string $link_text
   *   The link text.
   * @param Drupal\Core\Url|string $link_url
   *   The link url.
   */
  protected function notifyEmailFinish(EntityListInterface $entity_list, array $results, $email, $subject = NULL, $message = NULL, $link_text = NULL, $link_url = NULL) {
    $subject = $subject ?: $this->t('@label: @action: Finished', [
      '@label' => $entity_list->label(),
      '@action' => $this->label(),
    ]);
    $message = $message ?: $this->t('The "%action" action triggered from the <a href="@url">%label</a> has finished processing.', [
      '%label' => $entity_list->label(),
      '%action' => $this->label(),
      '@url' => $entity_list->toUrl()->setAbsolute()->toString(),
    ]);
    return $entity_list->notifyEmail($email, $subject, $message, $link_text, $link_url);
  }

  /**
   * Act as another user.
   *
   * Useful when running as job.
   *
   * @param string $user_id
   *   The user id to act as.
   *
   * @return $this
   */
  protected function userSwitch($user_id) {
    // Always run as admin.
    $this->currentUser = User::load(\Drupal::currentUser()->id());
    $act_user = User::load($user_id);
    \Drupal::currentUser()->setAccount($act_user);
    return $this;
  }

  /**
   * Restore original user.
   *
   * @return $this
   */
  protected function userRestore() {
    if (isset($this->currentUser)) {
      // Return authentication to previous user.
      \Drupal::currentUser()->setAccount($this->currentUser);
      unset($this->currentUser);
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(ExoListBuilderInterface $exo_list) {
    return TRUE;
  }

  /**
   * Load entity.
   *
   * @param string $entity_type
   *   The entity type id.
   * @param string $entity_id
   *   The entity id.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The entity.
   */
  protected function loadEntity($entity_type, $entity_id) {
    return $this->entityTypeManager->getStorage($entity_type)->load($entity_id);
  }

}
