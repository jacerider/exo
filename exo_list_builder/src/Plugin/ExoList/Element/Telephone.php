<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Element;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\exo_list_builder\Plugin\ExoListElementContentBase;

/**
 * Defines a eXo list element for rendering a content entity field.
 *
 * @ExoListElement(
 *   id = "telephone",
 *   label = @Translation("Telephone"),
 *   description = @Translation("Render the telephone number as a link."),
 *   weight = 0,
 *   field_type = {
 *     "telephone",
 *   },
 *   entity_type = {},
 *   bundle = {},
 *   field_name = {},
 *   exclusive = FALSE,
 * )
 */
class Telephone extends ExoListElementContentBase {

  /**
   * {@inheritdoc}
   */
  protected function viewItem(EntityInterface $entity, FieldItemInterface $field_item, array $field) {
    // If the telephone number is 5 or less digits, parse_url() will think
    // it's a port number rather than a phone number which causes the link
    // formatter to throw an InvalidArgumentException. Avoid this by inserting
    // a dash (-) after the first digit - RFC 3966 defines the dash as a
    // visual separator character and so will be removed before the phone
    // number is used. See https://bugs.php.net/bug.php?id=70588 for more.
    // While the bug states this only applies to numbers <= 65535, a 5 digit
    // number greater than 65535 will cause parse_url() to return FALSE so
    // we need the work around on any 5 digit (or less) number.
    // First we strip whitespace so we're counting actual digits.
    $phone_number = preg_replace('/\s+/', '', $field_item->value);
    if (strlen($phone_number) <= 5) {
      $phone_number = substr_replace($phone_number, '-', 1, 0);
    }
    return Link::fromTextAndUrl($field_item->value, Url::fromUri('tel:' . rawurlencode($phone_number)))->toString();
  }

}
