<?php

namespace Drupal\exo_imagine\PathProcessor;

use Drupal\image\PathProcessor\PathProcessorImageStyles;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines a path processor to rewrite image styles URLs.
 */
class PathProcessorWebpImageStyles extends PathProcessorImageStyles {

  /**
   * {@inheritdoc}
   */
  public function processInbound($path, Request $request) {
    $directory_path = $this->streamWrapperManager->getViaScheme('public')->getDirectoryPath();
    if (strpos($path, '/' . $directory_path . '/exowebp/styles/') === 0) {
      $path_prefix = '/' . $directory_path . '/exowebp/styles/';
    }
    // Check if the string '/system/files/exowebp/styles/' exists inside the path,
    // that means we have a case of private file's image style.
    elseif (strpos($path, '/system/files/exowebp/styles/') !== FALSE) {
      $path_prefix = '/system/files/exowebp/styles/';
      $path = substr($path, strpos($path, $path_prefix), strlen($path));
    }
    else {
      return $path;
    }

    // Strip out path prefix.
    $rest = preg_replace('|^' . preg_quote($path_prefix, '|') . '|', '', $path);

    // Get the image style, scheme and path.
    if (substr_count($rest, '/') >= 2) {
      [$image_style, $scheme, $file] = explode('/', $rest, 3);

      // Set the file as query parameter.
      $request->query->set('file', $file);

      return $path_prefix . $image_style . '/' . $scheme;
    }
    else {
      return $path;
    }
  }

}
