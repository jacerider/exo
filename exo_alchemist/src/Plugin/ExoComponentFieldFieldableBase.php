<?php

namespace Drupal\exo_alchemist\Plugin;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\exo_alchemist\ExoComponentFieldManager;
use Drupal\exo_alchemist\ExoComponentValue;
use Drupal\exo_alchemist\ExoComponentValues;

/**
 * Base class for Component Field plugins.
 */
abstract class ExoComponentFieldFieldableBase extends ExoComponentFieldBase implements ExoComponentFieldFieldableInterface, ExoComponentFieldFormInterface {

  use ExoComponentFieldFormTrait;

  /**
   * {@inheritdoc}
   */
  public function getStorageConfig() {
    return $this->pluginDefinition['storage'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldConfig() {
    return $this->pluginDefinition['field'];
  }

  /**
   * {@inheritdoc}
   */
  public function getWidgetConfig() {
    return $this->pluginDefinition['widget'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormatterConfig() {
    return $this->pluginDefinition['formatter'];
  }

  /**
   * {@inheritdoc}
   */
  public function onFieldClean(FieldItemListInterface $items, $update = TRUE) {
    foreach ($items as $delta => $item) {
      $this->cleanValue($item, $delta, $update);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function populateValues(ExoComponentValues $values, FieldItemListInterface $items) {
    $field = $values->getDefinition();
    ExoComponentFieldManager::setVisibleFieldName($items->getEntity(), $field->getName());
    // When an item is empty, we populate the defaults.
    if (!$field->getDefaults()) {
      $count = $field->getCardinality() > 1 ? $field->getCardinality() : 1;
      for ($delta = 0; $delta < $count; $delta++) {
        if ($value = $this->getDefaultValue($delta)) {
          $values->set($delta, $value);
          if (!$field->isRequired() && $field->isHideable()) {
            ExoComponentFieldManager::setHiddenFieldName($items->getEntity(), $field->getName());
          }
        }
      }
    }
    if ($field->isHiddenByDefault()) {
      ExoComponentFieldManager::setHiddenFieldName($items->getEntity(), $field->getName());
    }
    if ($field->getAdditionalValue('cleanup') === FALSE && !$items->isEmpty()) {
      return $items->getValue();
    }
    foreach ($items as $delta => $item) {
      // If we do have incoming values for an item, we want to clean it
      // as if we are uninstalling it.
      $this->cleanValue($item, $delta, $values->has($delta));
    }
    return $this->getValues($values, $items);
  }

  /**
   * Check if defaults exist for this field.
   *
   * @return bool
   *   TRUE if defaults exist.
   */
  protected function hasDefault() {
    return $this->getFieldDefinition()->hasDefault();
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultValue($delta = 0) {
    return [];
  }

  /**
   * Extending classes can use this method to clean existing values.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   The field item.
   * @param int $delta
   *   The field item delta.
   * @param bool $update
   *   TRUE if called when updating.
   */
  protected function cleanValue(FieldItemInterface $item, $delta, $update = TRUE) {}

  /**
   * {@inheritdoc}
   */
  public function formSubmit(array $form, FormStateInterface $form_state) {
    $field = $this->getFieldDefinition();
    /** @var \Drupal\Core\Field\FieldItemListInterface $items */
    $items = $form_state->get('component_entity')->get($field->safeId());
    if ($items->isEmpty()) {
      // When a field has been set to "empty", we place back in the defaults
      // and then hide the field so that it can later be restored.
      $values = ExoComponentValues::fromFieldDefaults($field);
      $items->setValue($this->populateValues($values, $items));

    }
  }

  /**
   * {@inheritdoc}
   */
  public function getValues(ExoComponentValues $values, FieldItemListInterface $items) {
    $field_values = [];
    foreach ($values as $delta => $value) {
      $this->validateValue($value);
      $item = $items->offsetExists($delta) ? $items->get($delta) : NULL;
      $field_values[$delta] = $this->getValue($value, $item);
    }
    return $field_values;
  }

  /**
   * {@inheritdoc}
   */
  public function validateValue(ExoComponentValue $value) {
  }

  /**
   * Extending classes can use this method to set individual values.
   *
   * @param \Drupal\exo_alchemist\ExoComponentValue $value
   *   The field value.
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   The current item.
   *
   * @return mixed
   *   A value suitable for setting to \Drupal\Core\Field\FieldItemInterface.
   */
  protected function getValue(ExoComponentValue $value, FieldItemInterface $item = NULL) {
    return $value->toArray();
  }

  /**
   * {@inheritdoc}
   */
  public function onFieldRestore(ExoComponentValues $values, FieldItemListInterface $items) {
    $field_values = [];
    if ($items->isEmpty()) {
      $field_values = $this->populateValues($values, $items);
    }
    return $field_values;
  }

  /**
   * {@inheritdoc}
   */
  public function onClone(FieldItemListInterface $items, $all = FALSE) {
    foreach ($items as $item) {
      $item->setValue($this->onCloneValue($item, $all));
    }
  }

  /**
   * Extending classes can use this method to clone existing values.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   The field item.
   * @param bool $all
   *   Flag that determines if this is a partial clone or full clone.
   *
   * @return mixed
   *   A value suitable for setting to \Drupal\Core\Field\FieldItemInterface.
   */
  protected function onCloneValue(FieldItemInterface $item, $all) {
    return $item->getValue();
  }

  /**
   * Swap entity field items.
   *
   * Support entity field overrides. For a component, if entity_field has been
   * defined for a given field, get the parent entity, see if the parent
   * has the field, make sure the types match, and then set the value.
   */
  protected function entityFieldValuesSwap(FieldItemListInterface $items, array $contexts) {
    $parent_context = $contexts['layout_builder.entity'] ?? $contexts['entity'] ?? NULL;
    if ($parent_context) {
      $entity_field_name = $this->getFieldDefinition()->getEntityField();
      if ($entity_field_name) {
        $parent = $parent_context->getContextValue();
        if ($parent->isNew()) {
          return;
        }
        if ($parent->hasField($entity_field_name)) {
          $this->setEditable(FALSE);
          if (in_array($parent->getFieldDefinition($entity_field_name)->getType(), [
            'entity_reference',
            'entity_reference_revisions',
          ])) {
            $parent_items = $parent->get($entity_field_name);
            if (!empty($parent_items->entity) && $parent_items->entity->hasField($entity_field_name)) {
              $parent = $parent_items->entity;
            }
          }
          $parent_items = $parent->get($entity_field_name);
          if ($this->isLayoutBuilder($contexts)) {
            // Do not use cached parent.
            $parent = \Drupal::entityTypeManager()->getStorage($parent->getEntityTypeId())->loadUnchanged($parent->id());
          }
          if ($parent->get($entity_field_name)->isEmpty()) {
            if ($this->getFieldDefinition()->isEntityFieldOptional()) {
              return;
            }
            $items->setValue(NULL);
            return;
          }
          $entity_field_match = $this->getFieldDefinition()->getEntityFieldMatch();
          if ($values = $this->entityFieldValuesMatch($entity_field_match, $parent, $parent_items, $items, $contexts)) {
            $items->setValue($values);
          }
        }
      }
    }
  }

  /**
   * Instances can use this method to match values.
   */
  protected function entityFieldValuesMatch($entity_field_match, ContentEntityInterface $parent, FieldItemListInterface $parent_items, FieldItemListInterface $items, array $contexts) {
    $parent_field_type = $parent_items->getFieldDefinition()->getType();
    $field_name = $this->getFieldDefinition()->safeId();
    $field_type = $items->getEntity()->getFieldDefinition($field_name)->getType();
    $values = [];
    if (is_string($entity_field_match)) {
      // If explicitly calling 'title', we assume entity title.
      if ($entity_field_match === 'entity_label') {
        $entity = $parent_items->first()->entity;
        if ($entity) {
          $values[] = $entity->label();
        }
        return $values;
      }
      foreach ($parent_items as $parent_item) {
        $entity = $parent_item->entity;
        if ($entity && $entity->hasField($entity_field_match) && $entity->get($entity_field_match)->entity) {
          $values[] = $entity->get($entity_field_match)->entity;
        }
      }
      return $values;
    }
    if ($field_type === $parent_field_type) {
      $values = $parent_items->getValue();
    }
    else {
      $string_types = ['string', 'string_long'];
      if (in_array($field_type, $string_types) && in_array($parent_field_type, $string_types)) {
        $values = $parent_items->getValue();
      }
    }
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function view(FieldItemListInterface $items, array $contexts) {
    // Allow component to act before values are built.
    if ($handler = $this->getFieldDefinition()->getComponent()->getHandler()) {
      $handler->fieldPreViewAlter($this, $items->getEntity(), $contexts);
    }

    $this->entityFieldValuesSwap($items, $contexts);

    $output = [];
    if ($items->count()) {
      foreach ($items as $delta => $item) {
        $value = $this->viewValue($item, $delta, $contexts);
        if (!empty($value)) {
          $output[$delta] = $value;
        }
      }
    }
    if (empty($output) && ($value = $this->viewEmptyValue($contexts))) {
      $output[0] = $value;
    }
    return $output;
  }

  /**
   * {@inheritdoc}
   */
  public function viewValue(FieldItemInterface $item, $delta, array $contexts) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function viewEmptyValue(array $contexts) {
    if ($this->isRequired() && $this->isLayoutBuilder($contexts)) {
      return [
        'render' => $this->componentPlaceholder($this->getFieldDefinition()->getLabel(), $this->t('This field has no value.')),
      ];
    }
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getRequiredPaths() {
    $paths = [];
    $field = $this->getFieldDefinition();
    $delta = 0;
    if ($this->isRequired() && $field->isEditable() && !$this->hasDefault() && empty($this->getDefaultValue($delta))) {
      $paths[] = $this->getItemParentsAsPath($delta);
    }
    return $paths;
  }

  /**
   * {@inheritdoc}
   */
  public function onPreSaveLayoutBuilderEntity(FieldItemListInterface $items, EntityInterface $parent_entity) {}

  /**
   * {@inheritdoc}
   */
  public function onPostSaveLayoutBuilderEntity(FieldItemListInterface $items, EntityInterface $parent_entity) {}

  /**
   * {@inheritdoc}
   */
  public function onPostDeleteLayoutBuilderEntity(FieldItemListInterface $items, EntityInterface $parent_entity) {}

  /**
   * {@inheritdoc}
   */
  public function onDraftUpdateLayoutBuilderEntity(FieldItemListInterface $items) {}

  /**
   * {@inheritdoc}
   */
  public function access(FieldItemListInterface $items, array $contexts, AccountInterface $account = NULL, $return_as_object = FALSE) {
    $account = $account ?: \Drupal::currentUser();
    $access = $this->componentAccess($items, $contexts, $account);
    return $return_as_object ? $access : $access->isAllowed();
  }

  /**
   * Indicates whether the field should be shown.
   *
   * Fields with specific access checking should override this method rather
   * than access(), in order to avoid repeating the handling of the
   * $return_as_object argument.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   *   The field items.
   * @param \Drupal\Core\Plugin\Context\Context[] $contexts
   *   An array of current contexts.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user session for which to check access.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   The access result.
   *
   * @see self::access()
   */
  protected function componentAccess(FieldItemListInterface $items, array $contexts, AccountInterface $account) {
    // By default, the field is visible.
    return AccessResult::allowed();
  }

}
