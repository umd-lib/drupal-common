<?php

namespace Drupal\bento\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing;
use Drupal\Core\Url;
use Drupal\Core\Entity\Element\EntityAutocomplete;
use Drupal\Core\Form\FormBase;

/**
 * Provides the Bento Looking More block.
 *
 * @Block(
 *   id = "bento_looking_more",
 *   admin_label = @Translation("Bento: Looking for More"),
 *   category = @Translation("Bento Quick Search"),
 * )
 */
class BentoLookingMoreBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Form builder service.
   *
   * @var Drupal\Core\Plugin\ContainerFactoryPluginInterface
   */
  protected $formBuilder;

  /**
   * Constructor.
   *
   * @param array $configuration
   *   Configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The id for the plugin.
   * @param mixed $plugin_definition
   *   The definition of the plugin implementaton.
   * @param Drupal\Core\Form\FormBuilderInterface $formBuilder
   *   The "form_builder" service instance to use.
   */
  public function __construct(
      array $configuration,
      $plugin_id,
      $plugin_definition,
      FormBuilderInterface $formBuilder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->formBuilder = $formBuilder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('form_builder'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $blockConfig = $this->getConfiguration();
    $looking_more_text = $blockConfig['looking_more_text'];
    $looking_more_url = $blockConfig['looking_more_url'];
    $looking_more_heading = $blockConfig['looking_more_heading'];
    $query = \Drupal::request()->query->get('query');
    $url_with_query = str_replace('%placeholder%', $query, $looking_more_url);

    return [
      '#theme' => 'bento_looking_more_block',
      '#looking_more_text' => $looking_more_text,
      '#looking_more_url' => $url_with_query,
      '#looking_more_heading' => $looking_more_heading,
      '#query' => $query,
      '#cache' => [
        'max-age' => 0,
      ],
    ];
  }

  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();

    $form['looking_more_heading'] = [
      '#type' => 'textfield',
      '#title' => t('Looking More Heading'),
      '#default_value' =>  !empty($config['looking_more_heading']) ? $config['looking_more_heading'] : null,
      '#required' => true,
    ];
    $form['looking_more_text'] = [
      '#type' => 'textfield',
      '#title' => t('Looking More Text'),
      '#default_value' =>  !empty($config['looking_more_text']) ? $config['looking_more_text'] : null,
      '#description' => t('Text to use for link text.'),
      '#required' => true,
    ];
    $form['looking_more_url'] = [
      '#type' => 'textarea',
      '#title' => t('Looking More URL'),
      '#default_value' =>  !empty($config['looking_more_url']) ? $config['looking_more_url'] : null,
      '#description' => t('URL for search. Use %placeholder% to indicate query placeholder.'),
      '#required' => true,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $heading = $form_state->getValue('looking_more_heading');
    $url = $form_state->getValue('looking_more_url');
    $text = $form_state->getValue('looking_more_text');
    $this->setConfigurationValue('looking_more_heading', $heading);
    $this->setConfigurationValue('looking_more_url', strip_tags($url));
    $this->setConfigurationValue('looking_more_text', $text);
  }
}
