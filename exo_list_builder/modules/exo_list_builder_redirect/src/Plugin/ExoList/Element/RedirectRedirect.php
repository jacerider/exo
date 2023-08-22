<?php

namespace Drupal\exo_list_builder_redirect\Plugin\ExoList\Element;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\exo_list_builder\Plugin\ExoListElementContentBase;

/**
 * Defines a eXo list element for rendering a content entity field.
 *
 * @ExoListElement(
 *   id = "redirect_redirect",
 *   label = @Translation("Redirect Link"),
 *   description = @Translation("Render redirect link."),
 *   weight = 0,
 *   field_type = {},
 *   entity_type = {
 *     "redirect",
 *   },
 *   bundle = {},
 *   field_name = {
 *     "redirect_redirect",
 *   },
 *   exclusive = FALSE,
 * )
 */
class RedirectRedirect extends ExoListElementContentBase {

  /**
   * Allow field to be linked to entity.
   *
   * @var bool
   */
  protected $allowEntityLink = FALSE;

  /**
   * {@inheritdoc}
   */
  protected function viewItem(EntityInterface $entity, FieldItemInterface $field_item, array $field) {
    /** @var \Drupal\link\Plugin\Field\FieldType\LinkItem $field_item */
    $output = [];
    $output['system']['#markup'] = urldecode($field_item->uri);
    $output['link'] = [
      '#type' => 'link',
      '#url' => $field_item->getUrl(),
      '#title' => $field_item->getUrl()->toString(),
      '#prefix' => ' (<small>',
      '#suffix' => '</small>)',
    ];
    return $output;
  }

}
