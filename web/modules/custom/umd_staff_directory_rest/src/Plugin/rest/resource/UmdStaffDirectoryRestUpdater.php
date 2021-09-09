<?php

namespace Drupal\umd_staff_directory_rest\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use \Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Provides a UMD Staff Directory Updater REST Resource
 *
 * @RestResource(
 *   id = "umd_staff_directory_rest_updater",
 *   label = @Translation("UMD Staff Directory Rest Updater"),
 *   uri_paths = {
 *     "create" = "/directory/updater"
 *   }
 * )
 */
class UmdStaffDirectoryRestUpdater extends ResourceBase {
  /**
   * Responds to POST requests.
   * @return \Drupal\rest\ResourceResponse
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function post(Array $parsed_json) {
    \Drupal::service('page_cache_kill_switch')->trigger();

    $now = (new \DateTime())->format('Y-m-d H:i:s');
    $response = ['message' => '***UmdStaffDirectoryRestUpdater POST Hello, this is a rest service - ' . $now ];
    return new ResourceResponse($response);
  }
}
