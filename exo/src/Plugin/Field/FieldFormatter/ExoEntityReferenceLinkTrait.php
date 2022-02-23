<?php

namespace Drupal\exo\Plugin\Field\FieldFormatter;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a trait for selecting which entities to view.
 */
trait ExoEntityReferenceLinkTrait {

  /**
   * {@inheritdoc}
   */
  public function linkSettingsSummary() {
    $summary = [];

    $link_types = [
      'content' => t('Linked to content'),
      'file' => t('Linked to file'),
    ] + $this->getLinkFieldOptions();
    // Display this setting only if image is linked.
    $image_link_setting = $this->getSetting('image_link');
    if (isset($link_types[$image_link_setting])) {
      $summary[] = $link_types[$image_link_setting];
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function linkSettingsForm(array &$form, FormStateInterface $form_state) {
    $elements = [];

    if (isset($form['image_link'])) {
      if ($options = $this->getLinkFieldOptions()) {
        $form['image_link']['#options'] += $options;
      }
    }

    return $elements;
  }

  /**
   * Get link field options.
   */
  public function getLinkFieldOptions() {
    $options = [];
    $fields = \Drupal::service('entity_field.manager')->getFieldDefinitions($this->fieldDefinition->getTargetEntityTypeId(), $this->fieldDefinition->getTargetBundle());
    foreach ($fields as $field_name => $field) {
      if ($field->getType() == 'link') {
        $options[$field->getName()] = $this->t('Field @label', ['@label' => $field->getLabel()]);
      }
    }
    return $options;
  }

}
