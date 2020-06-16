<?php

namespace Drupal\mirador_viewer\Plugin\views\argument;

use Drupal\views\Plugin\views\argument\ArgumentPluginBase;
use Drupal\search_api\Plugin\views\argument\SearchApiStandard;
use Drupal\mirador_viewer\Controller\DisplayMiradorController;
use Symfony\Component\DependencyInjection\ContainerInterface;

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

    // $plugin->setDateFormatter($container->get('date.formatter'));
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

    $c = new DisplayMiradorController();
    $fcid = $c->generateFedoraDatabaseDocumentID($id);

dsm($fcid);

    $this->query->addCondition($this->realField, $fcid, '=');
  }

}

