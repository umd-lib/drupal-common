<?php

namespace Drupal\umd_twig_filters\TwigExtension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class UMDTwigFilters extends AbstractExtension {
  public function getFilters(): array {
    $filters = [];
    $filters [] = new TwigFilter('base64_encode', function (string $encode) {
      return base64_encode($encode);
    });
    return $filters;
  }
}
