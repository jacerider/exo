<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Element;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\Sql\SqlEntityStorageInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_list_builder\EntityListInterface;
use Drupal\exo_list_builder\Plugin\ExoListElementBase;

/**
 * Defines a eXo list element for rendering a content entity field.
 *
 * @ExoListElement(
 *   id = "weight",
 *   label = @Translation("Weight"),
 *   description = @Translation("Render the weight as a form field with dragging."),
 *   weight = 0,
 *   field_type = {
 *     "integer",
 *     "config",
 *   },
 *   entity_type = {},
 *   bundle = {},
 *   field_name = {
 *    "weight",
 *    "field_weight",
 *   },
 *   exclusive = FALSE,
 * )
 */
class Weight extends ExoListElementBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'allow_reset' => FALSE,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, EntityListInterface $entity_list, array $field) {
    $form = parent::buildConfigurationForm($form, $form_state, $entity_list, $field);
    $configuration = $this->getConfiguration();
    $form['allow_reset'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow reset to alphabetical'),
      '#description' => $this->t('Will expose a button that will reset the weights to their default values.'),
      '#default_value' => $configuration['allow_reset'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function view(EntityInterface $entity, array $field) {
    $weight = $this->getWeight($entity, $field);
    return [
      '#type' => 'number',
      '#title' => t('Weight for @title', [
        '@title' => $entity->label(),
      ]),
      '#title_display' => 'invisible',
      '#default_value' => $weight,
      '#list_weight' => $weight,
      '#attributes' => ['class' => ['list-weight']],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function viewPlain(EntityInterface $entity, array $field) {
    return $this->getWeight($entity, $field);
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight(EntityInterface $entity, array $field) {
    $weight = 0;
    if ($entity instanceof ContentEntityInterface) {
      $weight = $entity->get($field['field_name'])->value;
    }
    elseif ($entity instanceof ConfigEntityInterface) {
      $weight = $entity->get($field['field_name']);
    }
    return $weight;
  }

  /**
   * {@inheritdoc}
   */
  protected function viewItem(EntityInterface $entity, FieldItemInterface $field_item, array $field) {
    return [
      '#type' => 'number',
      '#title' => t('Weight for @title', [
        '@title' => $field['label'],
      ]),
      '#title_display' => 'invisible',
      '#default_value' => $field_item->value,
      '#attributes' => ['class' => ['list-weight']],
    ];
  }

  /**
   * Reset weights.
   */
  public function resetWeights(EntityListInterface $entity_list, array $field) {
    $storage = $this->entityTypeManager()->getStorage($entity_list->getTargetEntityTypeId());
    if ($storage instanceof SqlEntityStorageInterface) {
      /** @var \Drupal\Core\Entity\Sql\DefaultTableMapping $table_mapping */
      $table_mapping = $storage->getTableMapping();
      $field_table = $table_mapping->getFieldTableName($field['field_name']);
      $query = \Drupal::database()->update($field_table);
      $query->fields([$field['field_name'] => 0]);
      if ($bundle_key = $entity_list->getTargetEntityType()->getKey('bundle')) {
        $bundle_table = $table_mapping->getFieldTableName($bundle_key);
        if ($bundle_table !== $field_table) {
          \Drupal::messenger()->addWarning('SQL weight joins not yet supported.');
          return;
        }
        $query->condition($bundle_key, $entity_list->getTargetBundleIds(), 'IN');
      }
      $query->execute();
    }
    else {
      \Drupal::messenger()->addWarning('Non SQL weight updating not yet supported.');
    }
  }

}
