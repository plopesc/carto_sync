<?php

namespace Drupal\carto_sync\Plugin\views\style;

use Drupal\views\Plugin\views\style\StylePluginBase;

/**
 * Defines a style plugin for CARTO Sync.
 *
 * @ViewsStyle(
 *   id = "carto_sync",
 *   theme = "views_view_unformatted",
 *   title = @Translation("CartoSync"),
 *   help = @Translation("CartoSync."),
 *   display_types = {"carto_sync"}
 * )
 */
class CartoSync extends StylePluginBase {

  /**
   * Does the style plugin allows to use style plugins.
   *
   * @var bool
   */
  protected $usesRowPlugin = TRUE;

  /**
   * Does the style plugin support custom css class for the rows.
   *
   * @var bool
   */
  protected $usesRowClass = FALSE;

}
