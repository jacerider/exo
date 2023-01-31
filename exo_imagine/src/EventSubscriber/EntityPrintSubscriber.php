<?php

namespace Drupal\exo_imagine\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\entity_print\Event\PrintEvents;
use Drupal\entity_print\Event\PrintHtmlAlterEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Masterminds\HTML5;

/**
 * The PostRenderSubscriber class.
 */
class EntityPrintSubscriber implements EventSubscriberInterface {

  /**
   * The Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * PostRenderSubscriber constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(ConfigFactoryInterface $config_factory, RequestStack $request_stack) {
    $this->configFactory = $config_factory;
    $this->requestStack = $request_stack;
  }

  /**
   * Post render.
   */
  public function postRender(PrintHtmlAlterEvent $event) {
    // We apply the fix to PHP Wkhtmltopdf and any engine when run in CLI.
    $config = $this->configFactory->get('entity_print.settings');
    if (
      $config->get('print_engines.pdf_engine') !== 'phpwkhtmltopdf' &&
      $event->getPhpSapi() !== 'cli'
    ) {
      return;
    }

    $html_string = &$event->getHtml();
    $html5 = new HTML5();
    $document = $html5->loadHTML($html_string);
    $pictures = $document->getElementsByTagName('picture');
    for ($i = $pictures->length; --$i >= 0;) {
      /** @var \DOMElement $picture */
      $picture = $pictures->item($i);
      $classes = $picture->getAttribute('class');
      if (strpos($classes, 'exo-imagine-') === FALSE) {
        continue;
      }
      /** @var \DOMElement $source */
      $source = $picture->getElementsByTagName('source')->item(0);
      if ($source->getAttribute('type') === 'image/webp') {
        $source = $picture->getElementsByTagName('source')->item(1);
      }
      /** @var \DOMElement $source */
      $srcset = $source->getAttribute('srcset');
      if ($srcset) {
        // Remove placeholder image.
        $picture->parentNode->removeChild($picture);
      }
      $srcset = $source->getAttribute('data-srcset');
      /** @var \DOMElement $image */
      $image = $picture->getElementsByTagName('img')->item(0);
      $attribute_value = $image->getAttribute('src');
      if ($attribute_value === 'about:blank' && $srcset) {
        $image->setAttribute('src', $srcset);
      }
    }

    // Overwrite the HTML.
    $html_string = $html5->saveHTML($document);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      PrintEvents::POST_RENDER => 'postRender',
    ];
  }

}
