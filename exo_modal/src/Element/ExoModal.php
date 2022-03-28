<?php

namespace Drupal\exo_modal\Element;

use Drupal\Core\Render\Element\RenderElement;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\Html;
use Drupal\Core\Url;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;

/**
 * Provides a render element that wraps child elements in an modal.
 *
 * @RenderElement("exo_modal")
 */
class ExoModal extends RenderElement {
  use ExoModalUrlTrait;

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#process' => [
        [$class, 'processGroup'],
        [$class, 'processModal'],
        [$class, 'processAjaxForm'],
      ],
      '#pre_render' => [
        [$class, 'preRenderGroup'],
        [$class, 'preRenderModal'],
      ],
      '#value' => NULL,
      '#theme_wrappers' => ['exo_modal_container'],
      '#title' => '',
      '#description' => '',
      '#use_close' => TRUE,
      '#trigger_text' => t('Open Modal'),
      '#trigger_icon' => '',
      '#trigger_icon_only' => FALSE,
      '#trigger_attributes' => [],
      '#modal_settings' => [],
      '#modal_attributes' => [],
      '#form_element' => TRUE,
      '#ajax_url' => NULL,
      '#view' => [
        'name' => NULL,
        'display_id' => NULL,
        'argument1' => NULL,
        'argument2' => NULL,
      ],
      '#entity' => [
        'entity_type' => NULL,
        'id' => NULL,
        'rid' => NULL,
        'display_id' => NULL,
      ],
      '#entity_edit' => [
        'entity_type' => NULL,
        'id' => NULL,
        'display_id' => NULL,
      ],
      '#entity_create' => [
        'entity_type' => NULL,
        'bundle' => NULL,
        'display_id' => NULL,
      ],
    ];
  }

  /**
   * Pre-render a modal element.
   *
   * @param array $element
   *   An associative array containing the properties and children of the
   *   element.
   *
   * @return array
   *   The modified element with all group members.
   */
  public static function preRenderModal(array $element) {
    // Generate the ID of the element if it's not explicitly given.
    $id = 'exo-modal-element';
    if (isset($element['#id'])) {
      $id = $element['#id'];
    }
    elseif (!empty($element['#parents'])) {
      $id = implode('-', $element['#parents']);
    }
    $element['#id'] = Html::getUniqueId($id);

    if (!empty($element['#title'])) {
      $element['#trigger_text'] = $element['#title'];
    }
    if (empty($element['#description'])) {
      $element['#description'] = '';
    }

    $element['#modal_settings'] += [
      'trigger' => [],
      'modal' => [],
    ];

    if (!empty($element['#ajax_url']) && $element['#ajax_url'] instanceof Url) {
      $element['#modal_settings']['modal']['contentAjax'] = static::toModalUrl($element['#ajax_url']);
    }

    // Views.
    if (!empty($element['#view']['name'])) {
      $element['#view'] += [
        'name' => NULL,
        'display_id' => NULL,
        'argument1' => NULL,
        'argument2' => NULL,
      ];
      $element['#use_close'] = FALSE;
      $display_id = !empty($element['#view']['display_id']) ? $element['#view']['display_id'] : 'default';
      $url = static::toModalUrl(Url::fromRoute('exo_modal.api.views.view', [
        'view' => $element['#view']['name'],
        'display_id' => $display_id,
        'argument1' => $element['#view']['argument1'],
        'argument2' => $element['#view']['argument2'],
      ]))->toString();
      $element['#modal_settings']['modal']['contentAjax'] = $url;
    }

    // Entity view.
    if (!empty($element['#entity']['id']) && !empty($element['#entity']['entity_type'])) {
      $element['#entity'] += [
        'entity_type' => NULL,
        'id' => NULL,
        'rid' => NULL,
        'display_id' => NULL,
      ];
      $query = $element['#entity']['query'] ?? [];
      if (empty($element['#entity']['no_destination'])) {
        $query = $query + \Drupal::destination()->getAsArray();
      }
      $element['#use_close'] = FALSE;
      $entity = \Drupal::entityTypeManager()->getStorage($element['#entity']['entity_type'])->load($element['#entity']['id']);
      if (!$entity->access('view')) {
        return [];
      }
      if ($entity->hasLinkTemplate('canonical')) {
        $element['#trigger_attributes']['href'] = $entity->toUrl()->toString();
      }
      $element['#modal_settings']['modal'] += [
        'title' => $entity->label(),
      ];
      $display_id = !empty($element['#entity']['display_id']) ? $element['#entity']['display_id'] : 'default';
      $url = static::toModalUrl(Url::fromRoute('exo_modal.api.entity.view', [
        'entity_type' => $element['#entity']['entity_type'],
        'entity' => $element['#entity']['id'],
        'revision_id' => !empty($element['#entity']['rid']) ? $element['#entity']['rid'] : NULL,
        'display_id' => $display_id,
      ], [
        'query' => $query,
      ]));
      $element['#modal_settings']['modal']['contentAjax'] = $url;
    }

    // Entity edit.
    if (!empty($element['#entity_edit']['id']) && !empty($element['#entity_edit']['entity_type'])) {
      $entity_type_id = $element['#entity_edit']['entity_type'] ?? NULL;
      $entity_id = $element['#entity_edit']['id'] ?? NULL;
      $display_id = $element['#entity_edit']['display_id'] ?? 'default';
      $access = $element['#entity_edit']['access_operation'] ?? 'update';
      $link = $element['#entity_edit']['link_id'] ?? $display_id . '-form';
      $query = $element['#entity_edit']['query'] ?? [];
      if (empty($element['#entity_edit']['no_destination'])) {
        $query = $query + \Drupal::destination()->getAsArray();
      }
      $element['#use_close'] = FALSE;
      $entity = \Drupal::entityTypeManager()->getStorage($entity_type_id)->load($entity_id);
      if ($access && !$entity->access($access)) {
        return [];
      }
      $url = Url::fromRoute('exo_modal.api.entity.edit', [
        'entity_type' => $entity_type_id,
        'entity' => $entity_id,
        'display_id' => $display_id,
        'access_id' => $access,
      ], [
        'query' => $query,
      ]);
      $element['#trigger_attributes']['href'] = $url->toString();
      $element['#modal_settings']['modal']['contentAjax'] = $url;
      $element['#modal_settings']['modal']['contentAjaxCache'] = FALSE;
      if ($link && $entity->hasLinkTemplate($link)) {
        $element['#trigger_attributes']['href'] = $entity->toUrl($link)->setOption('query', $query)->toString();
      }
    }

    // Entity delete.
    if (!empty($element['#entity_delete']['id']) && !empty($element['#entity_delete']['entity_type'])) {
      $entity_type_id = $element['#entity_delete']['entity_type'] ?? NULL;
      $entity_id = $element['#entity_delete']['id'] ?? NULL;
      $display_id = $element['#entity_delete']['display_id'] ?? 'default';
      $access = $element['#entity_delete']['access_operation'] ?? 'delete';
      $link = $element['#entity_delete']['link_id'] ?? $display_id . '-form';
      $query = $element['#entity_delete']['query'] ?? [];
      if (empty($element['#entity_delete']['no_destination'])) {
        $query = $query + \Drupal::destination()->getAsArray();
      }
      $element['#use_close'] = FALSE;
      $entity = \Drupal::entityTypeManager()->getStorage($entity_type_id)->load($entity_id);
      if ($access && !$entity->access($access)) {
        return [];
      }
      $url = Url::fromRoute('exo_modal.api.entity.delete', [
        'entity_type' => $entity_type_id,
        'entity' => $entity_id,
        'display_id' => $display_id,
        'access_id' => $access,
      ], [
        'query' => $query,
      ]);
      $element['#trigger_attributes']['href'] = $url->toString();
      $element['#modal_settings']['modal']['contentAjax'] = $url;
      $element['#modal_settings']['modal']['contentAjaxCache'] = FALSE;
      if ($link && $entity->hasLinkTemplate($link)) {
        $element['#trigger_attributes']['href'] = $entity->toUrl($link)->setOption('query', $query)->toString();
      }
    }

    // Entity add.
    if (!empty($element['#entity_create']['entity_type'])) {
      $entity_type_id = $element['#entity_create']['entity_type'] ?? NULL;
      $entity_type_manager = \Drupal::entityTypeManager();
      $entity_type = $entity_type_manager->getDefinition($entity_type_id);
      $bundle = $element['#entity_create']['bundle'] ?? NULL;
      $display_id = $element['#entity_create']['display_id'] ?? 'add';
      $link = $element['#entity_create']['link'] ?? $display_id . '-form';
      $query = $element['#entity_create']['query'] ?? [];
      if (empty($element['#entity_create']['no_destination'])) {
        $query = $query + \Drupal::destination()->getAsArray();
      }
      if (!empty($element['#entity_create']['data'])) {
        $query['data'] = $element['#entity_create']['data'];
      }
      $element['#use_close'] = FALSE;
      if (!$entity_type_manager->getAccessControlHandler($entity_type_id)->createAccess($bundle)) {
        return [];
      }
      $url = Url::fromRoute('exo_modal.api.entity.create', [
        'entity_type' => $entity_type_id,
        'bundle' => $bundle,
        'display_id' => $display_id,
      ], [
        'query' => $query,
      ]);
      $element['#modal_settings']['modal']['contentAjax'] = $url;
      $element['#modal_settings']['modal']['contentAjaxCache'] = FALSE;
      if (empty($element['#trigger_attributes']['href'])) {
        $element['#trigger_attributes']['href'] = $url->toString();
        if ($link && $entity_type->hasLinkTemplate($link)) {
          $entity = \Drupal::entityTypeManager()->getStorage($entity_type_id)->create();
          $route_id = str_replace(['-', 'drupal:'], ['_', ''], $link);
          try {
            $element['#trigger_attributes']['href'] = Url::fromRoute('entity.' . $entity_type_id . '.' . $route_id)->setOption('query', $query)->toString();
          }
          catch (MissingMandatoryParametersException $e) {
            // Fail silently.
          }
        }
      }
    }

    $element['#modal_settings']['modal'] += [
      'title' => $element['#trigger_text'],
      'subtitle' => $element['#description'],
    ];

    if (!empty($element['#modal_settings']['exo_preset'])) {
      $presets = exo_presets('exo_modal');
      $preset = $presets[$element['#modal_settings']['exo_preset']] ?? NULL;
      if (!empty($preset['modal']['padding'])) {
        $element['#modal_settings']['modal']['padding'] = $preset['modal']['padding'];
      }
    }
    if (!isset($element['#modal_settings']['modal']['padding'])) {
      $element['#modal_settings']['modal']['padding'] = '30px';
    }

    if (!empty($element['#use_close'])) {
      $element['_actions'] = [
        '#type' => 'actions',
        '#weight' => 1000,
      ];
      $element['_actions']['_close'] = [
        '#type' => 'exo_modal_close',
        '#label' => t('Close and Continue'),
        '#attributes' => ['class' => ['button--primary']],
      ];
    }

    if (!empty($element['#modal_settings']['modal']['contentAjax'])) {
      if ($element['#modal_settings']['modal']['contentAjax'] instanceof Url) {
        $element['#modal_settings']['modal']['contentAjax'] = static::toModalUrl($element['#modal_settings']['modal']['contentAjax'])->toString();
      }
      $element['#modal_settings']['modal']['contentAjax'] = ltrim($element['#modal_settings']['modal']['contentAjax'], '/');
    }

    return $element;
  }

  /**
   * Processes a container element.
   *
   * @param array $element
   *   An associative array containing the properties and children of the
   *   container.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $complete_form
   *   The complete form structure.
   *
   * @return array
   *   The processed element.
   */
  public static function processModal(array &$element, FormStateInterface $form_state, array &$complete_form) {
    if (!empty($element['#form_element'])) {
      $element['#attributes']['class'][] = 'js-form-item';
      $element['#attributes']['class'][] = 'form-item';
      $element['#attributes']['class'][] = 'js-form-wrapper';
      $element['#attributes']['class'][] = 'form-wrapper';
      $element['#modal_settings'] += [
        'trigger' => [],
        'modal' => [],
      ];
      if (empty($element['#modal_settings']['modal']['contentAjax'])) {
        $element['#modal_settings']['modal'] += [
          'nest' => TRUE,
          'appendTo' => 'form',
          'appendToOverlay' => 'form',
          'appendToNavigate' => 'form',
          'appendToClosest' => TRUE,
        ];
      }
    }
    return $element;
  }

}
