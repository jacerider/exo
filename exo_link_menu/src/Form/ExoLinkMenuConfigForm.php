<?php

namespace Drupal\exo_link_menu\Form;

use Drupal\Core\Field\FieldFilteredMarkup;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_icon\Entity\ExoIconPackage;

/**
 * Class ExoLinkMenuConfigForm.
 *
 * @package Drupal\exo_link_menu\Form
 */
class ExoLinkMenuConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'exo_link_menu.config',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'exo_link_menu_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('exo_link_menu.config');
    $form['icon'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Icon Packages'),
      '#description' => $this->t('Allow icon to be selected for menu items.'),
      '#default_value' => $config->get('icon') ?? 1,
    ];

    $form['packages'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Icon Packages'),
      '#description' => $this->t('The icon packages that should be made available to menu items. If no packages are selected, all will be made available.'),
      '#options' => ExoIconPackage::getLabels(),
      '#default_value' => $config->get('packages'),
      '#states' => [
        'visible' => [
          ':input[name="icon"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['target'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow target selection'),
      '#description' => $this->t('If selected, an "open in new window" checkbox will be made available.'),
      '#default_value' => $config->get('target'),
    ];

    $form['class'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow adding custom CSS classes'),
      '#description' => $this->t('If selected, a textfield will be provided that will allow adding in custom CSS classes.'),
      '#default_value' => $config->get('class'),
    ];

    $form['class_list'] = [
      '#type' => 'textarea',
      '#title' => $this->t('List of allowed CSS classes'),
      '#description' => $this->allowedValuesDescription(),
      '#default_value' => $config->get('class_list'),
      '#states' => [
        'visible' => [
          ':input[name="class"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['spacers'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow spacer links'),
      '#description' => $this->t('If enabled, menu links can be created as spacers.'),
      '#default_value' => $config->get('spacers'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function allowedValuesDescription() {
    $description = '<p>' . $this->t('The possible values this field can contain. Enter one value per line, in the format key|label.');
    $description .= '<br/>' . $this->t('The key is the stored value. The label will be used in displayed values and edit forms.');
    $description .= '<br/>' . $this->t('The label is optional: if a line contains a single string, it will be used as key and label.');
    $description .= '</p>';
    $description .= '<p>' . $this->t('Allowed HTML tags in labels: @tags', ['@tags' => FieldFilteredMarkup::displayAllowedTags()]) . '</p>';
    return $description;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('exo_link_menu.config')
      ->set('icon', !empty($form_state->getValue('icon')))
      ->set('packages', array_filter($form_state->getValue('packages')))
      ->set('target', !empty($form_state->getValue('target')))
      ->set('class', !empty($form_state->getValue('class')))
      ->set('class_list', $form_state->getValue('class_list'))
      ->set('spacers', !empty($form_state->getValue('spacers')))
      ->save();

    drupal_flush_all_caches();
  }

}
