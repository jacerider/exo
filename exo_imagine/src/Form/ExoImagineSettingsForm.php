<?php

namespace Drupal\exo_imagine\Form;

use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\exo\ExoSettingsFormBase;

/**
 * The eXo imagine settings form.
 */
class ExoImagineSettingsForm extends ExoSettingsFormBase {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('exo_imagine.settings')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $form['global'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Initialization settings'),
      '#weight' => -10,
      '#tree' => TRUE,
    ];

    $form['global']['webp'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable WebP Support'),
      '#default_value' => $this->exoSettings->getSetting('webp'),
      '#description' => $this->t('Automatically convert images to webp on supported browsers.'),
    ];

    $form['global']['webp_quality'] = [
      '#type' => 'number',
      '#title' => $this->t('WebP Quality'),
      '#default_value' => $this->exoSettings->getSetting('webp_quality'),
      '#description' => $this->t('Images will be encoded into WebP format if possible. This is the quality that will be used.'),
      '#min' => 1,
      '#max' => 100,
      '#step' => 1,
      '#states' => [
        'visible' => [
          ':input[name="global[webp]"]' => [
            'checked' => TRUE,
          ],
        ],
      ],
    ];

    /** @var \Drupal\exo_imagine\ExoImagineManager $manager */
    $manager = \Drupal::service('exo_imagine.manager');
    $imagine_styles = $manager->getImagineStyles();
    if (!empty($imagine_styles)) {
      /** @var \Drupal\Core\Datetime\DateFormatterInterface $date_formatter */
      $date_formatter = \Drupal::service('date.formatter');
      $form['styles'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Image Styles'),
        'table' => [
          '#type' => 'table',
          '#header' => [
            $this->t('Name'),
            $this->t('Last Used'),
            $this->t('Lock'),
            '',
          ],
        ],
      ];
      $destination = \Drupal::service('redirect.destination')->getAsArray();
      foreach ($imagine_styles as $imagine_style) {
        $row = [];
        $row[]['#markup'] = $imagine_style->label() . '<br><small>(' . $imagine_style->id() . ')</small>';
        $row[]['#markup'] = $date_formatter->format($imagine_style->getLastUsedTimestamp());
        $row[]['lock'] = [
          '#type' => 'checkbox',
          '#parents' => ['global', 'lock', $imagine_style->id()],
          '#default_value' => $this->exoSettings->getSetting([
            'lock',
            $imagine_style->id(),
          ]),
        ];
        $row[] = [
          '#type' => 'link',
          '#title' => $this->t('Delete'),
          '#url' => $imagine_style->getStyle()->toUrl('delete-form', [
            'query' => $destination,
          ]),
        ];
        $form['styles']['table'][$imagine_style->id()] = $row;
      }

      $form['actions']['purge'] = [
        '#type' => 'submit',
        '#value' => $this->t('Purge Styles'),
        '#submit' => ['::purgeStyles'],
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    $form_state->setValue([
      'global',
      'lock',
    ], array_filter($form_state->getValue([
      'global',
      'lock',
    ])));
    // Move instance settings into the global setting scope so that they get
    // saved.
    foreach ($form_state->getValue('global') as $setting => $value) {
      $form_state->setValue(['settings', $setting], $value);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function purgeStyles(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\exo_imagine\ExoImagineManager $manager */
    $manager = \Drupal::service('exo_imagine.manager');
    $imagine_styles = $manager->getImagineStyles();
    foreach ($imagine_styles as $imagine_style) {
      $style = $imagine_style->getStyle();
      if ($style->access('delete')) {
        $style->delete();
      }
    }

    // Make sure we don't have any old exo_image styles.
    $storage = \Drupal::entityTypeManager()->getStorage('image_style');
    $query = $storage->getQuery();
    $query->condition('name', 'exo_image_', 'STARTS_WITH');
    $entities = $storage->loadMultiple($query->execute());
    $storage->delete($entities);
  }

}
