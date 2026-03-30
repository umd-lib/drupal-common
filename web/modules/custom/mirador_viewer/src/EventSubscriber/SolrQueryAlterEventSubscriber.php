<?php

namespace Drupal\mirador_viewer\EventSubscriber;

use Drupal\search_api_solr\Event\PostConvertedQueryEvent;
use Drupal\search_api_solr\Event\PreQueryEvent;
use Drupal\search_api_solr\Event\PostExtractResultsEvent;
use Drupal\search_api_solr\Event\SearchApiSolrEvents;
use Drupal\search_api\Item\FieldInterface;
use Drupal\search_api\Query\QueryInterface as SapiQueryInterface;
use Solarium\Core\Query\QueryInterface as SolariumQueryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Alters the query where necessary to implement business logic.
 *
 * @package Drupal\mirador_viewer\EventSubscriber
 */
class SolrQueryAlterEventSubscriber implements EventSubscriberInterface {
  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      SearchApiSolrEvents::POST_CONVERT_QUERY => 'postConvQuery',
      SearchApiSolrEvents::POST_EXTRACT_RESULTS => 'postExtractResults',
      SearchApiSolrEvents::PRE_QUERY => 'preQuery',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function postExtractResults(PostExtractResultsEvent $event): void {
    // $results = $event->getSolariumResult();
    $results = $event->getSearchApiResultSet();
    if ($results->getResultCount() != 1) {
      return;
    }
    $result_items = $results->getResultItems();
    $new_files_array = [];
    foreach ($result_items as $key => $item) {
      $extra = $item->getExtraData('search_api_solr_document');
      if (!empty($extra)) {
        $members = $extra->__get('object__has_member');
        $body = NULL;
        $attachments = [];
        if (!empty($members)) {
          foreach ($members as $member) {
            $type = !empty($member['page__title__txt']) ? $member['page__title__txt'] : NULL;
            $files = !empty($member['page__has_file']) ? $member['page__has_file'] : [];
            if (empty($type) || empty($files)) {
              continue;
            }
            foreach ($files as $file) {
              $mime_type = !empty($file['file__mime_type__str']) ? $file['file__mime_type__str'] : NULL;
              if ($type == 'Body' && !empty($mime_type) && $mime_type == 'text/html') {
                $body_url = !empty($file['id']) ? $file['id'] : NULL;
                if (!empty($body_url)) {
                  $new_url = preg_replace(
                    '#^https?://[^/]+#',
                    'http://fcrepo-webapp:8080',
                    $body_url
                  );
                  $ch = curl_init();
                  curl_setopt($ch, CURLOPT_URL, $new_url);
                  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                  $body = curl_exec($ch);
                  curl_close($ch);
                }
              } elseif (str_contains($type, "Attachment") && !empty($file['id'])) {
                if (!empty($mime_type) && $mime_type == 'application/pdf') {
                  $attachments[] = "<a href='" . $file['id'] . "' >PDF Attachment</a>";
                } else {
                  $attachments[] = "<img src='" . $file['id'] . "' />";
                }
              }
            }
          }

          if (!empty($body)) {
            $item->setExcerpt($body . implode(" ", $attachments));
          }
          return;
        }

        // Legacy Support -- Remove post release
        $files = $extra->__get('files');
        if (!empty($files['docs'])) {
          foreach ($files['docs'] as $f_doc) {
            if (!empty($f_doc['mime_type']) && $f_doc['mime_type'] == 'application/pdf') {
              $new_files_array[] = $f_doc['id'];
            }
          }
        }
      }
      $pcdm_files = $item->getField('pcdm_files');
      if (!empty($pcdm_files)) {
         $pcdm_files->setValues($new_files_array);
      }
    }
    $results->setResultItems($result_items);
  }

  /**
   * {@inheritdoc}
   */
  public function preQuery(PreQueryEvent $event): void {
    $search_query = $event->getSearchApiQuery();
    $search_index = $search_query->getIndex();
    if (empty($search_index)) {
      return;
    }
    $index_id = $search_index->id();

    if ($index_id != 'fcrepo') {
      $server_id = $search_index->getServerId();
      if ($server_id != "fcrepo") {
        return;
      }

      $query = $event->getSolariumQuery();
      if (empty($query)) {
        return;
      }
      $query->addField('[child]');
      $query->addField('object__has_member');
      $query->addField('page__has_file');
      $query->addField('page__title__txt');
      $query->addField('id');
      $query->addField('file__mime_type__str');
      return;
    } 




    // Legacy Support -- Remove post release
    $query = $event->getSolariumQuery();
    $query->addField('annotation_source_type:[subquery]');
    $query->addField('files:[subquery]');
    $query->addParam('files.q', '{!terms f=id v=$row.pcdm_files}');
    $query->addParam('files.fl', 'id,title,filename,mime_type');
    $query->addParam('files.fq', 'mime_type:application/pdf');
    $keys = $search_query->getKeys();
    if (!empty($keys)) {
      if (!preg_match('/^(["\']).*\1$/m', $keys)) {
        $keys = $this->stripQueryChars($keys);
        $search_query->keys($keys);
      }
    }
  }

  public function stripQueryChars($keys) {
    $replace_me = [ '[', ']', '{', '}', '*', '+', '(', ')', ':', '%' ];
    if (!empty($keys)) {
      $keys = str_replace($replace_me, '', $keys);
    }
    return $keys;
  }


  /**
   * {@inheritdoc}
   */
  public function postConvQuery(PostConvertedQueryEvent $event): void {
    $query = $event->getSearchApiQuery();
    $search_index = $query->getIndex();
    if ($search_index) {
      $index_id = $search_index->id();
      if ($index_id != 'fcrepo') {
        return;
      }
    }
    $keys = $query->getKeys();
    $solarium_query = $event->getSolariumQuery();
    // $builder = $solarium_query->getRequestBuilder();
    $raw_query = $solarium_query->getQuery();
    $query_str = NULL;
    if (!empty($keys)) {
      // $query_str = "_query_:{!type=graph from=id to=extracted_text_source maxDepth=1 q.op=AND}" . str_replace('"', '', $this->stripQueryChars($keys)) . " ";
    } else {
      // $query_str = "_query_:{!type=graph from=id to=extracted_text_source maxDepth=1 q.op=AND} ";
    }

    if (!empty($raw_query)) {
      $query_str .= $raw_query;
    }

    $solarium_query->setQuery($query_str);
    // $r = $builder->build($solarium_query);
    // dsm($r);
    // dsm($r->getUri());
  }
}
