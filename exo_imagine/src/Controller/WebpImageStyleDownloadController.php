<?php

namespace Drupal\exo_imagine\Controller;

use Drupal\Component\Utility\Crypt;
use Drupal\Core\StreamWrapper\StreamWrapperManager;
use Drupal\image\Controller\ImageStyleDownloadController;
use Drupal\image\ImageStyleInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

/**
 * Defines a controller to serve image styles.
 */
class WebpImageStyleDownloadController extends ImageStyleDownloadController {

  /**
   * Generates a derivative, given a style and image path.
   *
   * After generating an image, transfer it to the requesting agent.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   * @param string $scheme
   *   The file scheme, defaults to 'private'.
   * @param \Drupal\image\ImageStyleInterface $image_style
   *   The image style to deliver.
   *
   * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Symfony\Component\HttpFoundation\Response
   *   The transferred file as response or some error response.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   *   Thrown when the file request is invalid.
   * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
   *   Thrown when the user does not have access to the file.
   * @throws \Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException
   *   Thrown when the file is still being generated.
   */
  public function deliver(Request $request, $scheme, ImageStyleInterface $image_style) {
    $original_target = $request->query->get('file');
    $original_uri = $scheme . '://' . $original_target;
    $target_sans_extension = rtrim($original_target, 'webp');
    $uri_sans_extension = rtrim($original_uri, 'webp');
    $target = NULL;
    $image_uri = NULL;

    // Find parent.
    if (file_exists($uri_sans_extension . 'jpg')) {
      $target = $target_sans_extension . 'jpg';
      $image_uri = $uri_sans_extension . 'jpg';
    }
    elseif (file_exists($uri_sans_extension . 'png')) {
      $target = $target_sans_extension . 'png';
      $image_uri = $uri_sans_extension . 'png';
    }
    elseif (file_exists($uri_sans_extension . 'gif')) {
      $target = $target_sans_extension . 'gif';
      $image_uri = $uri_sans_extension . 'gif';
    }
    if (!$target) {
      throw new NotFoundHttpException();
    }

    // Check that the style is defined, the scheme is valid, and the image
    // derivative token is valid. Sites which require image derivatives to be
    // generated without a token can set the
    // 'image.settings:allow_insecure_derivatives' configuration to TRUE to
    // bypass the latter check, but this will increase the site's vulnerability
    // to denial-of-service attacks. To prevent this variable from leaving the
    // site vulnerable to the most serious attacks, a token is always required
    // when a derivative of a style is requested.
    // The $target variable for a derivative of a style has
    // styles/<style_name>/... as structure, so we check if the $target variable
    // starts with styles/.
    $valid = !empty($image_style) && $this->streamWrapperManager->isValidScheme($scheme);
    if (!$this->config('image.settings')->get('allow_insecure_derivatives') || strpos(ltrim($target, '\/'), 'exowebp/styles/') === 0) {
      $valid &= hash_equals($image_style->getPathToken($image_uri), $request->query->get(IMAGE_DERIVATIVE_TOKEN, ''));
    }
    if (!$valid) {
      // Return a 404 (Page Not Found) rather than a 403 (Access Denied) as the
      // image token is for DDoS protection rather than access checking. 404s
      // are more likely to be cached (e.g. at a proxy) which enhances
      // protection from DDoS.
      throw new NotFoundHttpException();
    }

    $derivative_uri = $image_style->buildUri($image_uri);
    $info = pathinfo($derivative_uri);
    $derivative_uri = ($info['dirname'] ? $info['dirname'] . DIRECTORY_SEPARATOR : '') . $info['filename'] . '.webp';
    $headers = [];

    // If using the private scheme, let other modules provide headers and
    // control access to the file.
    if ($scheme == 'private') {
      $headers = $this->moduleHandler()->invokeAll('file_download', [$image_uri]);
      if (in_array(-1, $headers) || empty($headers)) {
        throw new AccessDeniedHttpException();
      }
    }

    // Don't try to generate file if source is missing.
    if (!file_exists($image_uri)) {
      // If the image style converted the extension, it has been added to the
      // original file, resulting in filenames like image.png.jpeg. So to find
      // the actual source image, we remove the extension and check if that
      // image exists.
      $path_info = pathinfo(StreamWrapperManager::getTarget($image_uri));
      $converted_image_uri = sprintf('%s://%s%s%s', $this->streamWrapperManager->getScheme($derivative_uri), $path_info['dirname'], DIRECTORY_SEPARATOR, $path_info['filename']);
      if (!file_exists($converted_image_uri)) {
        $this->logger->notice('Source image at %source_image_path not found while trying to generate derivative image at %derivative_path.', ['%source_image_path' => $image_uri, '%derivative_path' => $derivative_uri]);
        return new Response($this->t('Error generating image, missing source file.'), 404);
      }
      else {
        // The converted file does exist, use it as the source.
        $image_uri = $converted_image_uri;
      }
    }

    // Don't start generating the image if the derivative already exists or if
    // generation is in progress in another thread.
    if (!file_exists($derivative_uri)) {
      $lock_name = 'image_style_deliver:' . $image_style->id() . ':' . Crypt::hashBase64($image_uri);
      $lock_acquired = $this->lock->acquire($lock_name);
      if (!$lock_acquired) {
        // Tell client to retry again in 3 seconds. Currently no browsers are
        // known to support Retry-After.
        throw new ServiceUnavailableHttpException(3, 'Image generation in progress. Try again shortly.');
      }
    }

    // Try to generate the image, unless another thread just did it while we
    // were acquiring the lock.
    $success = file_exists($derivative_uri) || $image_style->createDerivative($image_uri, $derivative_uri);

    if (!empty($lock_acquired)) {
      $this->lock->release($lock_name);
    }

    if ($success) {
      // Generate a GD resource from the source image. You can't pass GD
      // resources created by the $imageFactory as a parameter to another
      // function, so we have to do everything in one function.
      $sourceImage = \Drupal::service('image.factory')->get($derivative_uri, 'gd');
      /** @var \Drupal\system\Plugin\ImageToolkit\GDToolkit $toolkit */
      $toolkit = $sourceImage->getToolkit();
      $sourceImage = $toolkit->getResource();
      $quality = \Drupal::service('exo_imagine.settings')->getSetting('webp_quality');
      if (function_exists('imagewebp') && @imagewebp($sourceImage, $derivative_uri, $quality)) {
        @imagedestroy($sourceImage);
      }
      elseif (extension_loaded('imagick')) {
        // phpcs:disable
        $image = new \Imagick($derivative_uri);
        $image->setImageFormat('webp');
        $image->setImageCompressionQuality($quality);
        $image->setImageAlphaChannel(\Imagick::ALPHACHANNEL_ACTIVATE);
        $image->setBackgroundColor(new \ImagickPixel('transparent'));
        // phpcs:enable
        $image->writeImage(\Drupal::service('file_system')->realpath($derivative_uri));
      }

      $image = $this->imageFactory->get($derivative_uri);
      $uri = $image->getSource();
      $headers += [
        'Content-Type' => $image->getMimeType(),
        'Content-Length' => $image->getFileSize(),
      ];
      // \Drupal\Core\EventSubscriber\FinishResponseSubscriber::onRespond()
      // sets response as not cacheable if the Cache-Control header is not
      // already modified. We pass in FALSE for non-private schemes for the
      // $public parameter to make sure we don't change the headers.
      return new BinaryFileResponse($uri, 200, $headers, $scheme !== 'private');
    }
    else {
      $this->logger->notice('Unable to generate the derived image located at %path.', ['%path' => $derivative_uri]);
      return new Response($this->t('Error generating image.'), 500);
    }
  }

}
