<?php

/**
 * @file
 * Builds placeholder replacement tokens for exo-media-related data.
 */

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Render\BubbleableMetadata;

/**
 * Impements hook_token_info_alter().
 */
function exo_alchemist_token_info_alter(&$data) {
  $data['tokens']['node']['exo_alchemist_smart_title'] = [
    'name' => t('eXo Alchemist: Smart Title'),
    'description' => t('Will determine the best title to use for representing this entity. Example: [node:exo_alchemist_smart_title:value]'),
    'type' => 'exo_alchemist',
  ];
  $data['tokens']['node']['exo_alchemist_smart_description'] = [
    'name' => t('eXo Alchemist: Smart Description'),
    'description' => t('Will determine the best description to use for representing this entity. Example: [node:exo_alchemist_smart_description:value]'),
    'type' => 'exo_alchemist',
  ];
  $data['tokens']['node']['exo_alchemist_smart_image'] = [
    'name' => t('eXo Alchemist: Smart Image URL'),
    'description' => t('Will determine the best image to use for representing this entity. Example: [node:exo_alchemist_smart_image:meta:url]'),
    'type' => 'exo_alchemist',
  ];
}

/**
 * Implements hook_tokens().
 */
function exo_alchemist_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $token_service = \Drupal::token();
  $replacements = [];
  if ($type == 'node' && !empty($data['node'])) {
    /** @var \Drupal\node\NodeInterface $node */
    $node = $data['node'];
    $lookup = [
      // Smart title.
      'exo_alchemist_smart_title' => [
        'alchemist_field_type' => 'text',
        'field_type' => 'text',
        'base_fields' => [
          'field_metatags:title',
          'field_meta:title',
        ],
        'fallback' => 'exo_alchemist_smart_title_fallback',
      ],
      // Smart image.
      'exo_alchemist_smart_image' => [
        'alchemist_field_type' => 'media_image',
        'field_type' => 'image',
        'base_fields' => [
          'field_banner',
          'field_featured_image',
          'field_image',
          'field_images',
          'field_file_image',
          'field_body',
        ],
        'fallback' => 'exo_alchemist_smart_image_fallback',
      ],
      // Smart description.
      'exo_alchemist_smart_description' => [
        'alchemist_field_type' => 'textarea',
        'field_type' => 'text_long',
        'base_fields' => [
          'field_metatags:description',
          'field_meta:description',
          'field_description',
          'field_teaser',
          'field_body',
        ],
        'truncate' => 160,
        'fallback' => 'exo_alchemist_smart_description_fallback',
      ],
    ];
    foreach ($lookup as $prefix => $info) {
      if ($found_tokens = $token_service->findWithPrefix($tokens, $prefix)) {
        $smart_data = exo_alchemist_smart_data($node, $info['alchemist_field_type'], $info['field_type'], $info['base_fields']);
        if ($smart_data) {
          if (!empty($smart_data['value'])) {
            $smart_replacements[$smart_data['fieldname']] = $smart_data['value'];
          }
          else {
            $entity_type = $smart_data['entity']->getEntityTypeId();
            $smart_tokens = [];
            foreach ($found_tokens as $key => $value) {
              $fieldname = $smart_data['fieldname'];
              if (!empty($smart_data['token'])) {
                $fieldname .= ':' . $smart_data['token'];
              }
              $smart_tokens[$smart_data['fieldname'] . ':' . $key] = '[' . $entity_type . ':' . $fieldname . ':' . $key . ']';
            }
            $smart_replacements = $token_service->generate($entity_type, $smart_tokens, [$entity_type => $smart_data['entity']], $options, $bubbleable_metadata);
          }
          if (count($found_tokens) === count($smart_replacements)) {
            foreach ($smart_replacements as &$replacement) {
              if (!empty($info['truncate']) && is_string($replacement)) {
                $replacement = str_replace('  ', ' ', str_replace('  ', ' ', str_replace([
                  "\r",
                  "\n",
                ], ' ', strip_tags($replacement))));
                $replacement = strlen($replacement) > $info['truncate'] ? substr($replacement, 0, $info['truncate']) . "..." : $replacement;
              }
            }
            $replacements += array_combine($found_tokens, $smart_replacements);
          }
        }
        elseif (!empty($info['fallback'])) {
          $replacements = $info['fallback']($replacements, $found_tokens, $data);
        }
      }
    }
  }
  return $replacements;
}

/**
 * Smart description fallback.
 */
function exo_alchemist_smart_title_fallback($replacements, array $found_tokens, array $data) {
  foreach ($found_tokens as $key => $value) {
    if (!empty($data['node'])) {
      if (\Drupal::service('path.matcher')->isFrontPage()) {
        $replacements[$value] = \Drupal::config('system.site')->get('name') . ' | ' . $data['node']->label();
      }
      else {
        $replacements[$value] = $data['node']->label() . ' | ' . \Drupal::config('system.site')->get('name');
      }
    }
  }
  return $replacements;
}

/**
 * Smart image fallback.
 */
function exo_alchemist_smart_image_fallback($replacements, array $found_tokens, array $data) {
  // Handle fallbacks.
  // @todo Open this open to other modules.
  if (\Drupal::moduleHandler()->moduleExists('real_favicon')) {
    $favicon = real_favicon_load_by_theme();
    if ($favicon) {
      foreach ($found_tokens as $key => $value) {
        $replacements[$value] = \Drupal::service('file_url_generator')->generateAbsoluteString($favicon->getManifestLargeImage());
      }
    }
  }
  return $replacements;
}

/**
 * Smart description fallback.
 */
function exo_alchemist_smart_description_fallback($replacements, array $found_tokens, array $data) {
  foreach ($found_tokens as $key => $value) {
    $replacements[$value] = \Drupal::config('system.site')->get('slogan');
  }
  return $replacements;
}

/**
 * Given an entity, find the best image file entity.
 *
 * @param \Drupal\Core\Entity\ContentEntityInterface $entity
 *   The entity.
 */
function exo_alchemist_smart_data(ContentEntityInterface $entity, $alchemist_field_type, $field_type, $base_fields = []) {
  $cache = &drupal_static(__FUNCTION__, []);
  $key = implode('_', [
    $entity->getEntityTypeId(),
    $entity->id(),
    $alchemist_field_type,
    $field_type,
    implode('_', $base_fields),
  ]);
  if (!isset($cache[$key])) {
    $data = [];
    foreach ($base_fields as $fieldname) {
      $parts = explode(':', $fieldname, 2);
      $fieldname = $parts[0];
      $path = isset($parts[1]) ? explode(':', $parts[1]) : [];
      if ($entity->hasField($fieldname)) {
        $base_data = exo_alchemist_smart_find($entity->get($fieldname), $field_type, $path);
        if ($base_data) {
          $data = $base_data;
          break;
        }
      }
    }
    if (empty($data)) {
      /** @var \Drupal\exo_alchemist\ExoComponentRepository $repository */
      $repository = \Drupal::service('exo_alchemist.repository');
      $items = $repository->getComponentItemsByFieldType($entity, $alchemist_field_type, FALSE, TRUE);
      if ($items) {
        $data = exo_alchemist_smart_find($items, $field_type);
      }
    }
    if (empty($data)) {
      /** @var \Drupal\exo_alchemist\ExoComponentRepository $repository */
      $repository = \Drupal::service('exo_alchemist.repository');
      $items = $repository->getComponentItemsByFieldType($entity, 'sequence', FALSE, TRUE);
      if ($items) {
        $entity = $items->first()->entity;
        if ($entity) {
          $items = $repository->getComponentItemsByEntityFieldType($entity, $alchemist_field_type);
          if ($items) {
            $data = exo_alchemist_smart_find($items, $field_type);
          }
        }
      }
    }
    $cache[$key] = $data;
  }
  return $cache[$key];
}

/**
 * Given a field, locate an image field.
 */
function exo_alchemist_smart_find(FieldItemListInterface $field, $field_type, $path = []) {
  if (!$field->isEmpty()) {
    switch ($field->getFieldDefinition()->getType()) {
      case 'metatag':
        $value = metatag_data_decode($field->first()->value) ?? [];
        $value = NestedArray::getValue($value, $path);
        if ($value) {
          return [
            'fieldname' => $field->getName(),
            'entity' => $field->getEntity(),
            'path' => $path,
            'value' => $value,
          ];
        }

      case 'entity_reference_revisions':
      case 'entity_reference':
        $subpath = $path;
        $subpath[] = $field->getName() . ':0:entity';
        $entity = $field->first()->entity;
        if ($entity && $entity instanceof ContentEntityInterface) {
          $fields = $entity->getFields(FALSE);
          uasort($fields, function ($a, $b) {
            return substr($a->getName(), -3) === '_fg' ? -1 : 1;
          });
          foreach ($fields as $subfield) {
            if ($field_type === 'image') {
              $image = exo_media_smart_image_find($subfield, $subpath);
              if ($image) {
                return $image;
              }
            }
          }
        }
        break;

      case $field_type:
        return [
          'fieldname' => $field->getName(),
          'entity' => $field->getEntity(),
          'path' => $path,
        ];
    }
  }
  return NULL;
}
