<?php

namespace Drupal\exo_list_builder_commerce\Plugin\ExoList\Element;

use Drupal\commerce_promotion\PromotionUsageInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\exo_list_builder\Plugin\ExoListElementContentBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a eXo list element for rendering a content entity field.
 *
 * @ExoListElement(
 *   id = "commerce_usage_limit",
 *   label = @Translation("Usage Limit"),
 *   description = @Translation("Render the usage limit."),
 *   weight = 0,
 *   field_type = {},
 *   entity_type = {
 *     "commerce_promotion",
 *   },
 *   bundle = {},
 *   field_name = {
 *     "usage_limit",
 *     "usage_limit_customer",
 *   },
 *   exclusive = FALSE,
 * )
 */
class PromotionUsageLimit extends ExoListElementContentBase implements ContainerFactoryPluginInterface {

  /**
   * The usage.
   *
   * @var \Drupal\commerce_promotion\PromotionUsageInterface
   */
  protected $usage;

  /**
   * LogGeneratorBase constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\commerce_promotion\PromotionUsageInterface $usage
   *   The usage information service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, PromotionUsageInterface $usage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->usage = $usage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('commerce_promotion.usage')
    );
  }

  /**
   * Get viewable output.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param array $field
   *   The field definition.
   *
   * @return mixed
   *   A renderable array or string.
   */
  protected function view(EntityInterface $entity, array $field) {
    /** @var \Drupal\commerce_promotion\Entity\PromotionInterface $entity */
    // Gets the promotion|coupon usage.
    if ($field['field_name'] === 'usage_limit') {
      $current_usage = $this->usage->load($entity);
      $usage_limit = $entity->getUsageLimit();
      $usage_limit = $usage_limit ?: $this->t('Unlimited');
      $usage = $current_usage . ' / ' . $usage_limit;
    }
    else {
      $usage = 'blek';
    }
    return $usage;
  }

}
