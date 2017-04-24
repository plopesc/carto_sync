<?php

namespace Drupal\carto_sync\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Link;
use Drupal\views\ViewEntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManager;

/**
 * Class CartoSyncItemListController.
 *
 * @package Drupal\carto_sync\Controller
 */
class CartoSyncItemListController extends ControllerBase {

  /**
   * Drupal\Core\Entity\EntityTypeManager definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManager $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * Listing.
   *
   * @return string
   *   Return Hello string.
   */
  public function listing() {

    return $this->render();
  }

  protected function render() {
    $views = $this->load();

    $list['#type'] = 'container';
    foreach ($views as $view_id => $data) {
      /** @var $view ViewEntityInterface*/
      $view = $data['view'];

      if ($view->access('update')) {
        $list[$view_id]['heading']['#markup'] = '<h2>' . Link::fromTextAndUrl($view->label(), $view->toUrl('edit-form'))->toString() . '</h2>';
      }
      else {
        $list[$view_id]['heading']['#markup'] = '<h2>' . $view->label() . '</h2>';
      }

      if (!empty($view->get('description'))) {
        $list[$view_id]['heading']['#markup'] .= '<span>' . $view->get('description') . '</span>';
      }
      $list[$view_id]['#type'] = 'container';
      $list[$view_id]['#attributes'] = ['class' => ['views-list-section', $view_id]];
      $list[$view_id]['table'] = [
        '#theme' => 'carto_sync_listing_table',
        '#headers' => $this->buildHeader(),
        '#attributes' => ['class' => ['views-listing-table', $view_id]],
      ];
      foreach ($data['displays'] as $display) {
        $list[$view_id]['table']['#rows'][$display['id']] = $this->buildRow($view, $display);
      }
    }
    // @todo Use a placeholder for the entity label if this is abstracted to
    // other entity types.
    $list['enabled']['table']['#empty'] = $this->t('There are no enabled views.');
    $list['disabled']['table']['#empty'] = $this->t('There are no disabled views.');

    return $list;
  }

  protected function load() {
    $entity_ids = $this->getEntityIds();
    $entities = $this->entityTypeManager->getStorage('view')->loadMultipleOverrideFree($entity_ids);

    $displays = [];
    foreach ($entities as $entity) {
      /**@var $entity ViewEntityInterface */
      foreach ($entity->get('display') as $id => $display) {
        if ($display['display_plugin'] == 'carto_sync') {
          if (!isset($displays[$entity->id()])) {
            $displays[$entity->id()]['view'] = $entity;
          }
          $displays[$entity->id()]['displays'][] = $entity->getDisplay($id);
        }
      }
    }

    return $displays;
  }

  /**
   * Loads entity IDs using a pager sorted by the entity id.
   *
   * @return array
   *   An array of entity IDs.
   */
  protected function getEntityIds() {
    $query = $this->entityTypeManager->getStorage('view')->getQuery()
      ->sort('id');

    return $query->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $view, array $display) {
    /*$client = \Drupal::httpClient();
    $settings = \Drupal::config('carto_sync.settings');
    $carto_id = $settings->get('carto_id');
    $carto_api_key = $settings->get('carto_api_key');

    $service = \Drupal::service('carto_sync.sql_api');
    $a = $service->getDatasetRows('untitled_table_5');


    $uri = 'https://'. $carto_id .'.carto.com/api/v2/sql?q=SELECT count(*) FROM '. 'untitled_table_5';
  // $data = $client->get($uri);
   //$d = json_decode($data->getBody());
    //$a =3;*/

    $service = \Drupal::service('carto_sync.sql_api');
    if (isset($display['display_options']['dataset_name'])) {
      if ($service->datasetExists($display['display_options']['dataset_name'])) {
        $url = $service->getDatasetUrl($display['display_options']['dataset_name']);
        $link = Link::fromTextAndUrl($this->t('View in CARTO'), $url);
      }
      else {
        $link = [
          '#plain_text' => 'cacas',
        ];
      }
    }

    return [
      'data' => [
        'display_name' => [
          'data' => [
            '#plain_text' => $view->label(),
          ],
        ],
        'dataset_name' => [
          'data' => [
            '#plain_text' => isset($display['display_options']['dataset_name']) ? $display['display_options']['dataset_name'] : $this->t('Not defined'),
          ],
        ],
        'status' => [
          'data' => [
            '#plain_text' => $view->get('description'),
          ],
        ],
        'operations' => [
          'data' => $link
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    return [
      'display_name' => [
        'data' => $this->t('Display Name'),
        '#attributes' => [
          'class' => ['carto-sync-name'],
        ],
      ],
      'dataset_name' => [
        'data' => $this->t('Carto Dataset'),
        '#attributes' => [
          'class' => ['carto-sync-machine-name'],
        ],
      ],
      'status' => [
        'data' => $this->t('Status'),
        '#attributes' => [
          'class' => ['carto-sync-description'],
        ],
      ],
      'operations' => [
        'data' => $this->t('Operations'),
        '#attributes' => [
          'class' => ['carto-sync-operations'],
        ],
      ],
    ];
  }

}
