<?php

namespace Drupal\exo_oembed\Controller;

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Render\HtmlResponse;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\media\IFrameMarkup;
use Drupal\media\IFrameUrlHelper;
use Drupal\media\OEmbed\ResourceException;
use Drupal\media\OEmbed\ResourceFetcherInterface;
use Drupal\media\OEmbed\UrlResolverInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * This is a clone of OEmbedIframeController.
 *
 * Its purpose is to add autoplay support. It can be replaced with something
 * more like:
 * https://www.drupal.org/project/media_oembed_control
 * when patch:
 * https://www.drupal.org/project/drupal/issues/3006729#comment-12815184
 * lands.
 */
class ExoOEmbedIframeController implements ContainerInjectionInterface {

  /**
   * The oEmbed resource fetcher service.
   *
   * @var \Drupal\media\OEmbed\ResourceFetcherInterface
   */
  protected $resourceFetcher;

  /**
   * The oEmbed URL resolver service.
   *
   * @var \Drupal\media\OEmbed\UrlResolverInterface
   */
  protected $urlResolver;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The logger channel.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The iFrame URL helper service.
   *
   * @var \Drupal\media\IFrameUrlHelper
   */
  protected $iFrameUrlHelper;

  /**
   * Constructs an OEmbedIframeController instance.
   *
   * @param \Drupal\media\OEmbed\ResourceFetcherInterface $resource_fetcher
   *   The oEmbed resource fetcher service.
   * @param \Drupal\media\OEmbed\UrlResolverInterface $url_resolver
   *   The oEmbed URL resolver service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger channel.
   * @param \Drupal\media\IFrameUrlHelper $iframe_url_helper
   *   The iFrame URL helper service.
   */
  public function __construct(ResourceFetcherInterface $resource_fetcher, UrlResolverInterface $url_resolver, RendererInterface $renderer, LoggerInterface $logger, IFrameUrlHelper $iframe_url_helper) {
    $this->resourceFetcher = $resource_fetcher;
    $this->urlResolver = $url_resolver;
    $this->renderer = $renderer;
    $this->logger = $logger;
    $this->iFrameUrlHelper = $iframe_url_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('media.oembed.resource_fetcher'),
      $container->get('media.oembed.url_resolver'),
      $container->get('renderer'),
      $container->get('logger.factory')->get('media'),
      $container->get('media.oembed.iframe_url_helper')
    );
  }

  /**
   * Renders an oEmbed resource.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The response object.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
   *   Will be thrown if the 'hash' parameter does not match the expected hash
   *   of the 'url' parameter.
   */
  public function render(Request $request) {
    $url = $request->query->get('url');
    $max_width = $request->query->getInt('max_width');
    $max_height = $request->query->getInt('max_height');

    // Hash the URL and max dimensions, and ensure it is equal to the hash
    // parameter passed in the query string.
    $hash = $this->iFrameUrlHelper->getHash($url, $max_width, $max_height);
    if (!hash_equals($hash, $request->query->get('hash', ''))) {
      throw new AccessDeniedHttpException('This resource is not available');
    }

    // Return a response instead of a render array so that the frame content
    // will not have all the blocks and page elements normally rendered by
    // Drupal.
    $response = new HtmlResponse();
    $response->addCacheableDependency(Url::createFromRequest($request));

    try {
      $resource_url = $this->urlResolver->getResourceUrl($url, $max_width, $max_height);
      $resource = $this->resourceFetcher->fetchResource($resource_url);

      $placeholder_token = Crypt::randomBytesBase64(55);

      $markup = IFrameMarkup::create($resource->getHtml());
      switch ($resource->getProvider()->getName()) {

        case 'YouTube':
          // We enable the Javascript API for YouTube.
          $markup = preg_replace('#\<iframe(.*?)\ssrc\=\"(.*?)\"(.*?)\>#i', '<iframe$1 src="$2&enablejsapi=1"$3>', $markup);

        case 'Vimeo':
          $markup = preg_replace('#\<iframe(.*?)\ssrc\=\"(.*?)\"(.*?)\>#i', '<iframe$1 src="$2&autoplay=1"$3>', $markup);
          break;
      }
      $markup = IFrameMarkup::create($markup);

      // Render the content in a new render context so that the cacheability
      // metadata of the rendered HTML will be captured correctly.
      $element = [
        '#theme' => 'media_oembed_iframe',
        // Even though the resource HTML is untrusted, IFrameMarkup::create()
        // will create a trusted string. The only reason this is okay is
        // because we are serving it in an iframe, which will mitigate the
        // potential dangers of displaying third-party markup.
        '#media' => $markup,
        '#cache' => [
          // Add the 'rendered' cache tag as this response is not processed by
          // \Drupal\Core\Render\MainContent\HtmlRenderer::renderResponse().
          'tags' => ['rendered'],
        ],
        '#attached' => [
          'html_response_attachment_placeholders' => [
            'styles' => '<css-placeholder token="' . $placeholder_token . '">',
          ],
          'library' => [
            'media/oembed.frame',
          ],
        ],
        '#placeholder_token' => $placeholder_token,
      ];
      $content = $this->renderer->executeInRenderContext(new RenderContext(), function () use ($resource, $element) {
        return $this->renderer->render($element);
      });
      $response
        ->setContent($content)
        ->setAttachments($element['#attached'])
        ->addCacheableDependency($resource)
        ->addCacheableDependency(CacheableMetadata::createFromRenderArray($element));
    }
    catch (ResourceException $e) {
      // Prevent the response from being cached.
      $response->setMaxAge(0);

      // The oEmbed system makes heavy use of exception wrapping, so log the
      // entire exception chain to help with troubleshooting.
      do {
        // @todo Log additional information from ResourceException, to help with
        // debugging, in https://www.drupal.org/project/drupal/issues/2972846.
        $this->logger->error($e->getMessage());
        $e = $e->getPrevious();
      } while ($e);
    }

    return $response;
  }

}
