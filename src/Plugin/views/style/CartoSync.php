<?php

namespace Drupal\carto_sync\Plugin\views\style;

use Drupal\views\Plugin\views\style\StylePluginBase;

/**
 * Defines a style plugin for CARTO Sync.
 *
 * @ViewsStyle(
 *   id = "carto_sync",
 *   theme = "views_view_carto_sync",
 *   title = @Translation("CartoSync"),
 *   help = @Translation("CartoSync."),
 *   display_types = {"carto_sync"}
 * )
 */
class CartoSync extends StylePluginBase {

}