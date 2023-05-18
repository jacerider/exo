<?php

namespace Drupal\exo_menu_component\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\system\Entity\Menu;

/**
 * Class MenuComponentTypeForm.
 */
class MenuComponentTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var \Drupal\exo_menu_component\Entity\MenuComponentTypeInterface $exo_menu_component_type */
    $exo_menu_component_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $exo_menu_component_type->label(),
      '#description' => $this->t("Label for the Menu Component type."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $exo_menu_component_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\exo_menu_component\Entity\MenuComponentType::load',
      ],
      '#disabled' => !$exo_menu_component_type->isNew(),
    ];

    $options = array_map(function ($menu) {
      return $menu->label();
    }, Menu::loadMultiple());
    asort($options);
    $form['targetMenu'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Target menu'),
      '#default_value' => $exo_menu_component_type->getTargetMenu(),
      '#options' => $options,
      '#required' => TRUE,
      '#description' => $this->t("Select the menus on which use this mega menu type."),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $exo_menu_component_type = $this->entity;
    $status = $exo_menu_component_type->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label Menu Component type.', [
          '%label' => $exo_menu_component_type->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Menu Component type.', [
          '%label' => $exo_menu_component_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($exo_menu_component_type->toUrl('collection'));
  }

}
