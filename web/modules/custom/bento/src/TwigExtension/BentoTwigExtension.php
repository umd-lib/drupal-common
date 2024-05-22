<?php

namespace Drupal\bento\TwigExtension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Twig extension providing custom functionalities.
 *
 * @package Drupal\bento\TwigExtension
 */
class BentoTwigExtension extends AbstractExtension {
  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'bento.twig_extension';
  }

  /**
   * Declare your custom twig filter here
   *
   * @return \Twig\TwigFilter[]
   *   TwigFilter array.
   */
  public function getFilters() {
    return [
      new TwigFilter(
        'bento_format',
        [$this, 'getBentoFormat']
      ),
    ];
  }

  /**
   * Returns the active language.
   *
   * @param string $raw_name
   *   the value from the api.
   * @return string
   *   value of bento formatted string
   */
  public function getBentoFormat(string $raw_name) {
  $format_map = [
      "archival_material" => "Archival Material",
      "article" => "Article",
      "audio" => "Audio",
      "audio_book" => "Audio Book",
      "book" => "Book",
      "cd" => "CD",
      "computer_file" => "Computer File",
      "dvd" => "DVD",
      "e_book" => "eBook",
      "e_music" => "eMusic",
      "e_video" => "eVideo",
      "image" => "Image",
      "journal" => "Journal",
      "journals" => "Journal",
      "lp" => "LP",
      "map" => "Map",
      "newspaper" => "Newspaper",
      "score" => "Score",
      "thesis" => "Thesis",
      "video_recording" => "Video Recording",
      "other" => "Other",
      "database" => "Database",
      "web_page" => "Webpage",
      "book_chapter" => "Book Chapter",
      "conference_proceeding" => "Conference Proceeding",
      "dissertation" => "Dissertation",
      "kit" => "Kit",
      "manuscript" => "Manuscript",
      "text_resource" => "Text Resource",
      "video" => "Video",
      "web_resource" => "Web Resource",
    ];
    return !empty($format_map[$raw_name]) ? $format_map[$raw_name] : "Other";
  }

}
