<?php

namespace Drupal\mirador_viewer\Plugin\views\argument;

use Drupal\views\Plugin\views\argument\ArgumentPluginBase;
use Drupal\search_api\Plugin\views\argument\SearchApiStandard;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\mirador_viewer\Utility\FedoraUtility;

/**
 * Contextual argument for a hash to fcrepo id lookup
 *
 * @ingroup views_argument_handlers
 *
 * @ViewsArgument("fcrepo_hash_reverse_id")
 */
class FcrepoHashReverse extends SearchApiStandard {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var static $plugin */
    $plugin = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    return $plugin;
  }

  /**
   * {@inheritdoc}
   */
  public function query($group_by = FALSE) {
    $this->fillValue();
    if ($this->value === FALSE) {
      $this->abort();
      return;
    }

    $id = reset($this->value);
    $fc = new FedoraUtility();
    $fcid = $fc->generateFedoraDatabaseDocumentID($id);
    $this->query->addCondition($this->realField, $fcid, '=');
  }

}

