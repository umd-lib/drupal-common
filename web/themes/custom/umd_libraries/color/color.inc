<?php
$info = [
  // Available colors and color labels used in theme.
  'fields' => [
    'header' => t('Header Color'),
  ],
  // Pre-defined color schemes.
  'schemes' => [
    'default' => [
      'title' => t('Default'),
      'colors' => [
         'header' => '#3f51b5',
         'secondary' => '#536dfe',
         'text' => '#3b3b3b',
         'background' => '#ffffff'
      ],
    ],
  ]
];
// CSS files (excluding @import) to rewrite with new color scheme.
$info['css'] = ['dist/css/color.css'];