<?php
/**
 * @file
 * Definition of Drupal\system_status\Controller\SystemStatusController
 */

 namespace Drupal\system_status\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\system_status\Form\SystemStatusSettingsForm;
 /**
  * Implementation of SystemStatusController
  */
  class SystemStatusController {

    protected $config;
  
    public function __construct() {
      $this->config = \Drupal::config('system_status.settings');
    }

    public function getJson(Request $request) {
      // Get the upstream status url
      $status_url = $this->getSystemStatusUrl();
      if ($status_url == null || $status_url == '') {
        return $this->errorResponse("Configuration of the upstream system status service url missing!");
      }
      // Get the status
      $curl = curl_init();
      curl_setopt($curl, CURLOPT_URL, $status_url);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
      $output = curl_exec($curl);

      if (curl_errno($curl)) {
        return $this->errorResponse("The curl request to $status_url failed with code: "
          . curl_errno($curl));
      } elseif (curl_getinfo($curl, CURLINFO_HTTP_CODE) != 200) {
        return $this->errorResponse("The upstream request $status_url failed with HTTP status code: "
          . curl_getinfo($curl, CURLINFO_HTTP_CODE));
      } else {
        $data = json_decode($output, true);
        $data['#cache'] = [
          'max-age' => 0, 
          'contexts' => [
            'url',
          ],
        ];
        $response = new CacheableJsonResponse($data);
        $response->addCacheableDependency(CacheableMetadata::createFromRenderArray($data));
        return $response;
      }
    }

    private function errorResponse($message) {
      return new JsonResponse([
        'error' => $message
      ]);
    }

    protected function getSystemStatusUrl() {
      return $this->config->get(SystemStatusSettingsForm::SERVICE_URL_FIELD);
    }
  }
