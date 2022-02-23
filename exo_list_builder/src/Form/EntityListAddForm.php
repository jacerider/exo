<?php

namespace Drupal\exo_list_builder\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Psr\Container\ContainerInterface;

/**
 * Class EntityListForm.
 */
class EntityListAddForm extends EntityForm {

  /**
   * The entity.
   *
   * @var \Drupal\exo_list_builder\EntityListInterface
   */
  protected $entity;

  /**
   * The entity type bundle info service.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * The entity manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.bundle.info'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * PatternEditForm constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle info service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity manager service.
   */
  public function __construct(EntityTypeBundleInfoInterface $entity_type_bundle_info, EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var \Drupal\exo_list_builder\EntityListInterface $exo_entity_list */
    $exo_entity_list = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $exo_entity_list->label(),
      '#description' => $this->t("Label for the eXo Entity List."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $exo_entity_list->id(),
      '#machine_name' => [
        'exists' => '\Drupal\exo_list_builder\Entity\EntityList::load',
      ],
      '#disabled' => !$exo_entity_list->isNew(),
    ];

    $entity_types = $this->entityTypeManager->getDefinitions();
    $options = [];
    foreach ($entity_types as $type) {
      $options[$type->id()] = $type->getLabel();
    }
    $form['target_entity_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Entity Type'),
      '#default_value' => $exo_entity_list->getTargetEntityTypeId(),
      '#options' => $options,
      '#required' => TRUE,
      '#disabled' => !$exo_entity_list->isNew(),
      '#ajax' => [
        'callback' => '::ajaxReplaceTargetBundles',
        'wrapper' => 'target-bundles',
        'method' => 'replace',
      ],
    ];

    $form['target_bundles_container'] = [
      '#type' => 'container',
      '#prefix' => '<div id="target-bundles">',
      '#suffix' => '</div>',
    ];

    if ($entity_type_id = $exo_entity_list->getTargetEntityTypeId()) {
      $entity_type = $entity_types[$entity_type_id];
      $form['target_bundles_container']['target_bundles_include'] = [
        '#type' => 'value',
        '#value' => [$entity_type_id => $entity_type_id],
      ];
      if ($entity_type->hasKey('bundle') && $bundles = $this->entityTypeBundleInfo->getBundleInfo($entity_type->id())) {
        $options = [];
        foreach ($bundles as $bundle_id => $bundle) {
          $options[$bundle_id] = $bundle['label'];
        }
        asort($options);
        $form['target_bundles_container']['target_bundles_include'] = [
          '#type' => 'select',
          '#title' => $this->t('Include Bundles'),
          '#description' => $this->t('If no bundles are select, all bundles will be included.'),
          '#default_value' => $exo_entity_list->getTargetBundleIncludeIds(),
          '#options' => $options,
          '#empty_option' => $this->t('- Select -'),
          '#multiple' => TRUE,
        ];
        $form['target_bundles_container']['target_bundles_exclude'] = [
          '#type' => 'select',
          '#title' => $this->t('Exclude Bundles'),
          '#default_value' => $exo_entity_list->getTargetBundleExcludeIds(),
          '#options' => $options,
          '#empty_option' => $this->t('- Select -'),
          '#multiple' => TRUE,
        ];
      }
    }

    return $form;
  }

  /**
   * Handles switching the type selector.
   */
  public function ajaxReplaceTargetBundles($form, FormStateInterface $form_state) {
    return $form['target_bundles_container'];
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $exo_entity_list = $this->entity;
    $status = $exo_entity_list->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label eXo Entity List.', [
          '%label' => $exo_entity_list->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label eXo Entity List.', [
          '%label' => $exo_entity_list->label(),
        ]));
    }
    $form_state->setRedirectUrl($exo_entity_list->toUrl('edit-form'));
  }

}
