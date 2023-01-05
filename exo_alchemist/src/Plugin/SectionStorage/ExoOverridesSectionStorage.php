<?php

namespace Drupal\exo_alchemist\Plugin\SectionStorage;

use Drupal\Core\Entity\RevisionableInterface;
use Drupal\exo_alchemist\ExoComponentSectionStorageInterface;
use Drupal\layout_builder\Plugin\SectionStorage\OverridesSectionStorage;

/**
 * Extends the 'overrides' section storage type.
 */
class ExoOverridesSectionStorage extends OverridesSectionStorage implements ExoComponentSectionStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function getStorageId() {
    $entity = $this->getEntity();
    return $entity->getEntityTypeId() . '.' . ($entity->isNew() ? $entity->uuid() : $entity->id());
  }

  /**
   * {@inheritdoc}
   */
  public function getTempstoreKey() {
    $key = parent::getTempstoreKey();
    $entity = $this->getEntity();
    if ($entity instanceof RevisionableInterface) {
      $key .= '.' . $entity->getRevisionId();
    }
    return $key;
  }

  /**
   * {@inheritdoc}
   */
  protected function getSectionList() {
    /** @var \Drupal\layout_builder\Field\LayoutSectionItemList $sections */
    $sections = $this->getEntity()->get(static::FIELD_NAME);
    // Layout settings are always inherited from the parent.
    $default_section_storage = $this->getDefaultSectionStorage();
    $default_sections = $default_section_storage->getSections();
    foreach ($sections->getSections() as $delta => $section) {
      if (isset($default_sections[$delta])) {
        $section->setLayoutSettings($default_sections[$delta]->getLayoutSettings());
      }
    }
    return $sections;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntity() {
    // Convert to public method.
    $entity = parent::getEntity();
    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getParentEntity() {
    // Parent entity is same as entity.
    return $this->getEntity();
  }

  /**
   * {@inheritdoc}
   */
  public function getViewMode() {
    return $this->getContextValue('view_mode');
  }

  /**
   * {@inheritdoc}
   */
  public function getRegionSize($delta, $region) {
    $section = $this->getSection($delta);
    $settings = $section->getLayoutSettings();
    return isset($settings['column_sizes'][$region]) ? $settings['column_sizes'][$region] : 'full';
  }

}
