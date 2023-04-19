<?php

namespace Drupal\exo_imagine;

/**
 * Image placeholder helper.
 */
class ExoImaginePlaceholder {

  /**
   * The text.
   *
   * @var string
   */
  protected $text = NULL;

  /**
   * The width.
   *
   * @var int
   */
  protected $width = 150;

  /**
   * The height.
   *
   * @var int
   */
  protected $height = NULL;

  /**
   * The background color.
   *
   * @var string
   */
  protected $backgroundColor = NULL;

  /**
   * The text color.
   *
   * @var string
   */
  protected $textColor = NULL;

  /**
   * Define the width of the placeholder.
   *
   * @param int $pixels
   *   The width in pixels.
   *
   * @return $this
   */
  public function width($pixels) {
    if (is_numeric($pixels) && $pixels > 0) {
      $this->width = $pixels;
    }

    return $this;
  }

  /**
   * Define the height of the placeholder.
   *
   * @param int $pixels
   *   The height in pixels.
   *
   * @return $this
   */
  public function height($pixels) {
    if (is_numeric($pixels) && $pixels > 0) {
      $this->height = $pixels;
    }

    return $this;
  }

  /**
   * Define the text of the placeholder.
   *
   * If you define the message as NULL, you'll see the width and height of the
   * placeholder. A string or empty string will overwrite the width and height.
   *
   * @param string $message
   *   The message you see in the placeholder.
   * @param string $color
   *   Define the color of the text in the placeholder in HEX.
   *
   * @return $this
   */
  public function text($message, $color = NULL) {
    if (!empty($message) && is_string($message)) {
      $this->text = $message;
    }

    if (!empty($color)) {
      $this->textColor($color);
    }

    return $this;
  }

  /**
   * Define the color of the text in the placeholder in HEX.
   *
   * @param string $color
   *   The color of the text in HEX.
   *
   * @return $this
   */
  public function textColor($color) {
    if (!empty($color) && preg_match('/[a-f0-9]{6}/i', $color) > 0) {
      $this->textColor = $color;
    }

    return $this;
  }

  /**
   * Define the background color of the placeholder in HEX.
   *
   * @param string $color
   *   The color of the placeholder background.
   *
   * @return $this
   *   The placeholder.
   */
  public function background($color) {
    if (!empty($color) && preg_match('/[a-f0-9]{6}/i', $color) > 0) {
      $this->backgroundColor = $color;
    }

    return $this;
  }

  /**
   * Render and return the image.
   *
   * In Base64 image only or include an optimized image html-tag.
   *
   * @param bool $imgTag
   *   TRUE to include the html image-tag.
   *
   * @return string
   *   The Base64 image or html image tag with the base64 image attached
   */
  public function render($imgTag = FALSE) {
    if (empty($this->height)) {
      $this->height = $this->width;
    }

    if ($this->text === NULL) {
      $this->text = $this->width . ' x ' . $this->height;
    }

    $this->width = round($this->width);
    $this->height = round($this->height);

    $image = imagecreatetruecolor($this->width, $this->height);

    if ($this->backgroundColor) {
      $bgHex = str_split($this->backgroundColor, 2);
      $bgColor = imagecolorallocate($image, hexdec($bgHex[0]), hexdec($bgHex[1]), hexdec($bgHex[2]));
      imagefilledrectangle($image, 0, 0, $this->width, $this->height, $bgColor);
    }
    else {
      $bgColor = imagecolorallocatealpha($image, 0, 0, 0, 127);
      imagesavealpha($image, TRUE);
      imagefill($image, 0, 0, $bgColor);
    }

    if ($this->textColor) {
      $textHex = str_split($this->textColor, 2);
      $textColor = imagecolorallocate($image, hexdec($textHex[0]), hexdec($textHex[1]), hexdec($textHex[2]));

      $textwidth = imagefontwidth(5) * strlen($this->text);
      $center = ceil($this->width / 2);
      $x = $center - (ceil($textwidth / 2));

      $center = ceil($this->height / 2);
      $y = $center - 6;

      imagestring($image, 5, $x, $y, $this->text, $textColor);
    }

    ob_start();
    imagepng($image);
    $contents = ob_get_contents();
    ob_end_clean();

    imagedestroy($image);

    $html = 'data:image/png;base64,' . base64_encode($contents);

    if ($imgTag === TRUE) {
      $html = '<img src="' . $html . '" width="' . $this->width . '" height="' . $this->height . '" alt="' . $this->text . '" title="' . $this->text . '" />';
    }
    return $html;
  }

  /**
   * Static function to render and return the image in Base64 image directly.
   *
   * @param int $width
   *   The width in pixels.
   * @param int $height
   *   The height in pixels.
   * @param string $background_color
   *   The color of the placeholder background.
   * @param string $textColor
   *   The color of the text in HEX.
   * @param string $text
   *   The message you see in the placeholder.
   *
   * @return string
   *   The Base64 image
   */
  public static function image($width = NULL, $height = NULL, $background_color = NULL, $textColor = NULL, $text = NULL) {
    $factory = new self();
    return $factory->width($width)
      ->height($height)
      ->background($background_color)
      ->text($text, $textColor)
      ->render();
  }

  /**
   * Static function to render and return the html image.
   *
   * Can retur tag with the Base64 image attached directly.
   *
   * @param int $width
   *   The width in pixels.
   * @param int $height
   *   The height in pixels.
   * @param string $background_color
   *   The color of the placeholder background.
   * @param string $textColor
   *   The color of the text in HEX.
   * @param string $text
   *   The message you see in the placeholder.
   *
   * @return string
   *   The Base64 image in a html image tag
   */
  public static function imagetag($width = NULL, $height = NULL, $background_color = NULL, $textColor = NULL, $text = NULL) {
    $factory = new self();
    return $factory->width($width)
      ->height($height)
      ->background($background_color)
      ->text($text, $textColor)
      ->render(TRUE);
  }

}
