<?php

namespace Drupal\exo_imagine;

use Drupal\file\FileInterface;
use Drupal\image_style_warmer\ImageStylesWarmer;

/**
 * Defines an images styles warmer.
 */
class ExoImagineStylesWarmer extends ImageStylesWarmer {

  /**
   * The image style entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $imageStyles;

  /**
   * {@inheritdoc}
   */
  public function doWarmUp(FileInterface $file, array $image_styles) {
    if (empty($image_styles) || !$this->validateImage($file)) {
      return;
    }

    /** @var \Drupal\exo_imagine\ExoImagineManager $imagine_manager */
    $imagine_manager = \Drupal::service('exo_imagine.manager');
    $supports_webp = $imagine_manager->supportsWebp();

    /** @var \Drupal\Core\Image\Image $image */

    // Create image derivatives if they not already exists.
    $styles = $this->imageStyles->loadMultiple($image_styles);
    $image_uri = $file->getFileUri();
    foreach ($styles as $style) {
      /** @var \Drupal\image\Entity\ImageStyle $style */
      $derivative_uri = $style->buildUri($image_uri);
      if ($supports_webp && $imagine_manager->getImagineStyleByStyleId($style->id())) {
        $webp_uri = $imagine_manager->toWebpUri($derivative_uri);
        if (!file_exists($webp_uri)) {
          $imagine_manager->generateWebp($style, $image_uri, $webp_uri);
        }
      }
      elseif (!file_exists($derivative_uri)) {
        $style->createDerivative($image_uri, $derivative_uri);
      }
    }
  }

}
