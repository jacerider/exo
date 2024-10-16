<?php

namespace Drupal\exo_alchemist\EventSubscriber;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\Plugin\Context\EntityContext;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\exo_alchemist\Cache\ExoCacheableContext;
use Drupal\exo_alchemist\ExoComponentManager;
use Drupal\layout_builder\Event\SectionComponentBuildRenderArrayEvent;
use Drupal\layout_builder\LayoutBuilderEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Builds render arrays and handles access for all block components.
 *
 * @internal
 *   Tagged services are internal.
 */
class BlockComponentRenderArrayAfterCore implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * The eXo component plugin manager.
   *
   * @var \Drupal\exo_alchemist\ExoComponentManager
   */
  protected $exoComponentManager;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs a new ExoComponentParamConverter object.
   *
   * @param \Drupal\exo_alchemist\ExoComponentManager $exo_component_manager
   *   The eXo component manager.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct(ExoComponentManager $exo_component_manager, AccountInterface $current_user) {
    $this->exoComponentManager = $exo_component_manager;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[LayoutBuilderEvents::SECTION_COMPONENT_BUILD_RENDER_ARRAY] = [
      'onBuildRender', 98,
    ];
    return $events;
  }

  /**
   * Builds render arrays for block plugins and sets it on the event.
   *
   * @param \Drupal\layout_builder\Event\SectionComponentBuildRenderArrayEvent $event
   *   The section component render event.
   */
  public function onBuildRender(SectionComponentBuildRenderArrayEvent $event) {
    $block = $event->getPlugin();
    if (!$block instanceof BlockPluginInterface) {
      return;
    }

    $build = $event->getBuild();
    $contexts = $event->getContexts();
    $entity = NULL;
    if (isset($build['content']['#block_content'])) {
      $entity = $build['content']['#block_content'];
      $definition = $entity->alchemistDefinition ?? NULL;
      if (empty($definition)) {
        /** @var \Drupal\exo_alchemist\ExoComponentManager $exo_component_manager */
        $plugin_id = $this->exoComponentManager->getPluginIdFromSafeId($entity->bundle());
        $definition = $this->exoComponentManager->getInstalledDefinition($plugin_id, FALSE);
      }
      if ($definition) {
        // Only check access if the component is not being previewed.
        if ($event->inPreview()) {
          $access = AccessResult::allowed()->setCacheMaxAge(0);
        }
        else {
          $access = $this->exoComponentManager->accessEntity($definition, $entity, $contexts, $this->currentUser, TRUE);
        }

        $event->addCacheableDependency($access);
        if ($access->isAllowed()) {
          $contexts['component_entity'] = EntityContext::fromEntity($entity);
          $contexts['component_id'] = new Context(new ContextDefinition('string'), $entity->id() ?? $entity->uuid());
          $cacheable_context = new ExoCacheableContext();
          $cacheable_context->setCacheableMetadata(new CacheableMetadata());
          $contexts['cacheable_metadata'] = new Context(new ContextDefinition('any'), $cacheable_context);
          $this->exoComponentManager->viewEntity($definition, $build['content'], $entity, $contexts);
          if ($event->inPreview()) {
            $preview_fallback_string = $this->t('"@block" component', ['@block' => $block->label()]);
            $build['content']['#wrapper_attributes']['data-layout-content-preview-placeholder-label'] = $preview_fallback_string;
          }
          $build['content']['#weight'] = $build['#weight'];
          $event->setBuild($build['content']);
          $event->addCacheableDependency($contexts['cacheable_metadata']->getContextValue()->getCacheableMetadata());
        }
        else {
          $event->setBuild([]);
        }
      }
    }
  }

}
