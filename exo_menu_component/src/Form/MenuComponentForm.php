<?php

namespace Drupal\exo_menu_component\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\system\MenuInterface;

/**
 * Form controller for menu component forms.
 *
 * @ingroup exo_menu_component
 */
class MenuComponentForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    /** @var \Drupal\menu_link_content\MenuLinkContentInterface $link */
    $menu_link = \Drupal::routeMatch()->getParameter('menu_link_content');
    $default_title = $menu_link ? $menu_link->getTitle() : NULL;

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Component Title'),
      '#default_value' => $default_title,
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {

    /** @var \Drupal\menu_link_content\MenuLinkContentInterface $menu_link */
    $menu_link = \Drupal::routeMatch()->getParameter('menu_link_content');
    if ($menu_link) {
      /** @var \Drupal\exo_menu_component\Entity\MenuComponentInterface $entity */
      $entity = &$this->entity;
      $status = parent::save($form, $form_state);
      $menu_id = $menu_link->getMenuName();
    }
    else {
      /** @var \Drupal\system\MenuInterface $menu */
      $menu = \Drupal::routeMatch()->getParameter('menu');
      if (!$menu instanceof MenuInterface) {
        $this->messenger()->addError($this->t('Component could not be associated with a menu.'));
        return;
      }
      $menu_id = $menu->id();

      /** @var \Drupal\exo_menu_component\Entity\MenuComponentInterface $entity */
      $entity = &$this->entity;
      $status = parent::save($form, $form_state);

      $menu_link = $this->entityTypeManager->getStorage('menu_link_content')->create([
        'bundle' => 'menu_link_content',
        'menu_name' => $menu_id,
        'title' => $form_state->getValue('title'),
        'link' => [
          'uri' => 'internal:#',
          'options' => [
            'attributes' => [
              'data-exo-menu-component' => $entity->id(),
            ],
          ],
        ],
        'weight' => 0,
      ]);
      $menu_link->save();
    }

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addStatus($this->t('Created the %label menu component.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        $this->messenger()->addStatus($this->t('Saved the %label Simple mega menu.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.menu.edit_form', ['menu' => $menu_id]);
  }

}
