<?php

namespace Drupal\exo_imagine\Entity;

/**
 * Provides an interface for defining order item rug decorators.
 *
 * @ingroup commerce_rug
 */
interface ExoImagineStyleInterface {

  /**
   * The imagine style id.
   *
   * @return string
   *   The imagine style id.
   */
  public function id();

  /**
   * The imagine style label.
   *
   * @return string
   *   The imagine style label.
   */
  public function label();

  /**
   * Get the image style.
   *
   * @return \Drupal\image\ImageStyleInterface
   *   The image style.
   */
  public function getStyle();

  /**
   * Get settings.
   *
   * @return array
   *   The settings.
   */
  public function getSettings();

  /**
   * Get the width.
   *
   * @return string
   *   The width.
   */
  public function getWidth();

  /**
   * Get the height.
   *
   * @return string
   *   The height.
   */
  public function getHeight();

  /**
   * Get the unique id.
   *
   * @return string
   *   The unique id.
   */
  public function getUnique();

  /**
   * Get the quality.
   *
   * @return string
   *   The quality.
   */
  public function getQuality();

  /**
   * Get the last used timestamp.
   *
   * @return int
   *   The last used timestamp.
   */
  public function getLastUsedTimestamp();

  /**
   * Set the last used timestamp.
   */
  public function setLastUsedTimestamp();

}
