<?php

/**
 * @file
 * Definition of Drupal\lib_cal\Helper\LibCalApiHelper
 */

namespace Drupal\lib_cal\Helper;

/**
 * Helper class for interacting with LibCal API
 */
class LibCalApiHelper {

  private $endpoint;
  private $client_id;
  private $client_secret;
  private $token;
  private $token_expiry;

  static $instance;

  public static function getInstance($endpoint, $client_id, $client_secret)
  {
    if (is_null( self::$instance) )
    {
      self::$instance = new self();
    }
    self::$instance->endpoint = $endpoint;
    self::$instance->client_id = $client_id;
    self::$instance->client_secret = $client_secret;
    return self::$instance;
  }

  private function isTokenValid() {
    if ($this->token != null && $this->token_expiry > time()) {
      return TRUE;
    }
    return FALSE;
  }

  private function getTokenString() {
    if (!$this->isTokenValid()) {
      $curr_time = time();
      $token_array = $this->requestToken();
      if ($token_array == null) {
        \Drupal::logger('lib_cal')->notice('LibCal API Token request failed!');
        return null;
      } else {
        $this->token = $token_array['access_token'];
        $this->token_expiry = $curr_time + $token_array['expires_in'];
      }
    }
    return $this->token;
  }

  public function requestToken() {
    $token_url = $this->endpoint . 'oauth/token';
    $params = [
      'client_id' => $this->client_id,
      'client_secret' => $this->client_secret,
      'grant_type' => 'client_credentials',
    ];
    return $this->curlRequest($token_url, false, $params);
  }

  public function getEvents($calendar_id, $limit=3) {
    $events_url = $this->endpoint . "events?cal_id=$calendar_id&limit=$limit";
    $response = $this->curlRequest($events_url, $this->getTokenString());
    $processed_events = null;
    if ($response != null) {
      $events = $response['events'];
      $processed_events = array_map(function ($event) {
        $date = date_create_from_format('Y-m-d\TH:i:sT', $event['start']);
        return [
          'id' => $event['id'],
          'title' => $event['title'],
          'month' => date_format($date, 'M'),
          'day' => date_format($date, 'j'),
          'hour' => date_format($date, 'g:iA'),
          'url' => $event['url']['public'],
          'category' => array_values(
            array_filter(
              array_map(
                function ($category) { 
                  if (!str_contains($category['name'], '>')) return $category['name']; 
                },
                $event['category']
              )
            )
          )
        ];
      }, $events);
    }
    return $processed_events;
  }

  private function arrayToParams($arr) {
    return join('&', array_map(function ($key, $val) { return "$key=$val";}, array_keys($arr), $arr));
  }
  
  private function curlRequest($url, $bearer_token, $post_fields = false) {
    $curl = curl_init();
    if ($bearer_token) {
      curl_setopt($curl, CURLOPT_HTTPHEADER, ["Authorization: Bearer $bearer_token"]);
    }
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    if ($post_fields && count($post_fields) > 0) {
      curl_setopt($curl, CURLOPT_POST, 1);
      curl_setopt($curl, CURLOPT_POSTFIELDS, $this->arrayToParams($post_fields));
    }
    $output = curl_exec($curl);
  
    if (curl_errno($curl)) {
      \Drupal::logger('lib_cal')->notice("The curl request to $url failed "
      . curl_errno($curl) . '. ' . curl_error($curl));
    } elseif (curl_getinfo($curl, CURLINFO_HTTP_CODE) != 200) {
      \Drupal::logger('lib_cal')->notice("The upstream request $url failed with HTTP status code: "
      . curl_getinfo($curl, CURLINFO_HTTP_CODE));
    } else {
      $data = json_decode($output, true);
      return $data;
    }
    return null;
  }
} 
