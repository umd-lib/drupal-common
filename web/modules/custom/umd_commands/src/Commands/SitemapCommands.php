<?php

namespace Drupal\umd_commands\Commands;

use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;
use Drupal\block\Entity\Block;
use Symfony\Component\Console\Input\InputOption;
use Drupal\simple_sitemap\Manager\Generator;
use GuzzleHttp\ClientInterface;

class SitemapCommands extends DrushCommands
{

  // Simple Sitemap Generator
  protected $generator;

  /**
   * HTTP client
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $client;

  /**
   * Sitemap command constructor.
   *
   * @param \Drupal\simple_sitemap\Manager\Generator $generator
   *   The simple_sitemap.generator service.
   *
   * @param \GuzzleHttp\Client $client
   *   HTTP client.
   */
  public function __construct(Generator $generator, ClientInterface $client) {
    $this->generator = $generator;
    $this->client = $client;

    parent::__construct();
  }

  /**
   * Add item to XML Sitemap. Arbitrary paths allowed.
   * Use with caution.
   *
   * @command umd-commands:sitemap-add-arbitrary
   *
   * @usage drush umd-commands:sitemap-add-arbitrary
   *
   * @aliases umd-sitemap-add-arbitrary
   *
   * @param $relative_url
   */
  public function sitemapAddArbitrary(string $relative_url = null) : void {
    $this->sitemapAdd($relative_url, FALSE);
  }

  /**
   * Add item to XML Sitemap. Verify it is a valid path.
   *
   * @command umd-commands:sitemap-add
   *
   * @usage drush umd-commands:sitemap-add
   *
   * @aliases umd-sitemap-add
   *
   * @param $relative_url
   */
  public function sitemapAdd(string $relative_url = null, bool $verify = TRUE) : void {
    if (empty($relative_url)) {
      $this->output()->writeln('Relative path missing from command.');
      return;
    }
    if ($this->isAbsolute($relative_url)) {
      $this->output()->writeln('Absolute URL detected. Only relative paths supported.');
      return;
    }
    if ($verify && !\Drupal::service('path.validator')->isValid($relative_url)) {
      $this->output()->writeln('Path does not appear to be a valid Drupal path.');
      return;
    }
    if ($verify && !$this->checkUrlExists($relative_url)) {
      $this->output()->writeln('Path does not resolve.');
      return;
    }
    $this->generator
      ->customLinkManager()
      ->setSitemaps(['default'])
      ->add($relative_url, ['priority' => 0.5, 'changefreq' => 'weekly']);
      $this->output()->writeln('Path added.');
  }

  /**
   * Check if absolute URL
   */
  function isAbsolute($url) {
    return isset(parse_url($url)['host']);
  }

  /**
   * Remove item from XML Sitemap
   *
   * @command umd-commands:sitemap-remove
   *
   * @usage drush umd-commands:sitemap-remove
   *
   * @aliases umd-sitemap-remove
   *
   * @param $relative_url
   */
  public function sitemapRemove(string $relative_url = null) : void {
    if (!empty($relative_url) && \Drupal::service('path.validator')->isValid($relative_url)) {
      $this->generator
        ->customLinkManager()
        ->setSitemaps(['default'])
        ->remove($relative_url);
        $this->output()->writeln('Path removed.');
        return;
    }
    $this->output()->writeln('Path empty or is invalid. Therefore, nothing done.');
  }

  protected function checkUrlExists(string $relative_url) {
    $path = 'http://localhost';
    $full_url = $path . $relative_url;
    $req = $this->client->request('GET', $full_url, ['http_errors' => FALSE]);
    if ($req->getStatusCode() != 200) {
      return FALSE;
    }
    return TRUE;
  }

}
