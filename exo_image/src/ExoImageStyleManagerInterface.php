<?php

namespace Drupal\exo_image;

/**
 * Interface ExoImageStyleManagerInterface.
 */
interface ExoImageStyleManagerInterface {

  /**
   * Creates a WebP copy of a source image URI.
   *
   * @param string $uri
   *   Image URI.
   * @param int $quality
   *   Image quality factor.
   *
   * @return bool|string
   *   The location of the WebP image if successful, FALSE if not successful.
   */
  public function createWebpCopy($uri, $quality = NULL);

  /**
   * Try and find an image style that matches the requested dimensions.
   *
   * @param int $width
   *   The width.
   * @param int $height
   *   The height.
   * @param int $quality
   *   The image quality.
   *
   * @return mixed
   *   A matching image style or NULL if none was found.
   */
  public function findImageStyle($width = NULL, $height = NULL, $quality = NULL);

}
