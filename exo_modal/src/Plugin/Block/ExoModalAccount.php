<?php

namespace Drupal\exo_modal\Plugin\Block;

use Drupal\Core\Url;
use Drupal\exo_modal\Plugin\ExoModalBlockBase;

/**
 * Provides a block to display login/account within a modal.
 *
 * @Block(
 *   id = "exo_modal_account",
 *   admin_label = @Translation("eXo Modal Account"),
 * )
 */
class ExoModalAccount extends ExoModalBlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
      'block' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function buildModalContent() {
    $build = [];

    $account = \Drupal::currentUser();
    if ($account->isAnonymous()) {
      /** @var \Drupal\Core\Form\FormBuilderInterface $form_builder */
      $form_builder = \Drupal::service('form_builder');
      $build['login'] = [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#attributes' => [
          'class' => ['exo-modal-first', 'exo-modal-account--login'],
        ],
      ];
      $build['login']['title'] = [
        '#type' => 'html_tag',
        '#tag' => 'h2',
        '#attributes' => [
          'class' => ['form--title'],
        ],
        '#value' => $this->t('Log In'),
        '#weight' => -100,
      ];
      $build['login']['form'] = $form_builder->getForm('\Drupal\exo_modal\Form\ExoModalAccountLoginForm', $this->configuration['modal']);

      if (\Drupal::service('access_check.user.register')->access($account)->isAllowed()) {
        $build['create'] = [
          '#type' => 'html_tag',
          '#tag' => 'div',
          '#attributes' => [
            'class' => ['exo-modal-second', 'exo-modal-account--create'],
          ],
        ];

        $build['create']['title'] = [
          '#type' => 'html_tag',
          '#tag' => 'h2',
          '#attributes' => [
            'class' => ['form--title'],
          ],
          '#value' => $this->t('New here?'),
          '#weight' => -100,
        ];
        $build['create']['register'] = [
          '#type' => 'exo_modal',
          '#id' => 'exo_modal_account_create',
          '#title' => $this->t('Create an account'),
          '#ajax_url' => Url::fromRoute('exo_modal.api.account.register'),
          '#modal_settings' => [
            'modal' => [
              'inherit' => TRUE,
            ],
          ],
          '#trigger_attributes' => [
            'href' => Url::fromRoute('user.register')->toString(),
          ],
          '#form_element' => FALSE,
        ];
      }
    }
    else {
      /** @var \Drupal\user\UserInterface $user */
      $user = $this->entityTypeManager->getStorage('user')->load($account->id());
      $build['account'] = [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#attributes' => [
          'class' => ['exo-modal-first', 'exo-modal-account--account'],
        ],
      ];
      $build['account']['welcome'] = [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#attributes' => [
          'class' => ['exo-modal-account--welcome'],
        ],
        'message' => [
          '#markup' => $this->t('Hello, <span>@name</span>', ['@name' => $user->getDisplayName()]),
        ],
      ];
      $build['account']['user'] = $this->entityTypeManager->getViewBuilder('user')->view($user, 'default');

      $build['account']['menu'] = \Drupal::service('exo_menu.generator')->generate('simple', 'tree', [
        'account',
      ], ['depth' => 1])->toRenderable();

      $build['logout'] = [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#attributes' => [
          'class' => ['exo-modal-second', 'exo-modal-account--logout'],
        ],
      ];
      $build['logout']['link'] = [
        '#type' => 'link',
        '#title' => $this->icon('Log out')->setIcon('regular-sign-out'),
        '#url' => Url::fromRoute('user.logout'),
      ];
    }

    \Drupal::moduleHandler()->alter('exo_modal_account_block', $build);

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  protected function generateModal($with_content = TRUE) {
    $modal = parent::generateModal($with_content);
    $account = \Drupal::currentUser();
    if ($account->isAnonymous()) {
      $modal->setTriggerAttribute('href', Url::fromRoute('user.login')->setOption('query', \Drupal::destination()->getAsArray())->toString());
    }
    else {
      $modal->setTriggerText($this->t('<span class="account--title">@title</span> <span class="account--name">@name</span>', [
        '@title' => $this->configuration['modal']['trigger']['text'],
        '@name' => $account->getDisplayName(),
      ]));
      $modal->setModalSetting('title', $this->t('My Account'));
      $modal->setTriggerAttribute('href', Url::fromRoute('user.page')->toString());
    }
    return $modal;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    $contexts = parent::getCacheContexts();
    $contexts[] = 'user';
    return $contexts;
  }

}
