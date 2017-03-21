<?php

namespace Drupal\carto_sync\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\carto_sync\CartoSyncViewListBuilder;

/**
 * Class CartoSyncViewListBuilder.
 *
 * @package Drupal\carto_sync\Controller
 */
class CartoSyncViewListController extends ControllerBase {

  /**
   * Listing all the CARTO synced Views.
   *
   * @return array
   *   A render array as expected by drupal_render().
   */
  public function listing() {
    $definition = $this->entityTypeManager()->getDefinition('view');
    $handler = new CartoSyncViewListBuilder($definition,$this->entityTypeManager()->getStorage('view'));
    return $handler->render();
  }

}
